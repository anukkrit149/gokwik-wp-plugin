<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-11:29 PM
 * Date-13-02-2021
 * File-WC_Gokwik_Gateway_COD.php
 * Project-wp-gokwik-woocommerce
 * Copyrights Reserved
 * Created by PhpStorm
 *
 *
 * Working-
 *********************/

require __DIR__.'./../gokwik-php-sdk/Gokwik.php';
require __DIR__.'/utility.php';
use Gokwik\Api\App;

class WC_Gateway_COD extends WC_Payment_Gateway {

	/**
	 * WC_Gokwik_Gateway_COD constructor.
	 */
	const WP_ORDER = 'wp_order';
	public static $log = false;
	/**
	 * @var bool
	 */
	private static $log_enabled;
	/**
	 * @var string
	 */
	public $app_id,$app_secret,$merchant_id;
	/**
	 * @var bool
	 */
	private $test_mode,$debug;


	public function __construct() {
		$this->id = 'cod';
		$this->icon               = "https://cdn.gokwik.co/logo/gokwik-cod-logo.gif";
		$this->method_title       = __( 'Gokwik - COD', 'woocommerce' );
		$this->method_description = __( 'Allow customers to securely pay via using Gokwik COD', 'woocommerce' );
		$this->has_fields         = true;
		$this->siteUrl = get_site_url();

		$this->init_form_fields();
		$this->init_settings();
		// Values that needs to be set by the merchant
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		$this->merchant_id        = $this->get_option( 'merchant_id' );
		$this->app_id             = $this->get_option( 'app_id' );
		$this->app_secret         = $this->get_option( 'app_secret' );
		$this->test_mode          = 'yes' === $this->get_option( 'testmode' );
		$this->debug              = 'yes' === $this->get_option( 'debug', 'no' );
		self::$log_enabled        = $this->debug;
		$this->instructions       = $this->get_option( 'instructions' );
		$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';
		$this->supports           = array('products');
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
//		add_action( 'wp_enqueue_scripts', array( $this, 'gokwik_scripts' ) );
		if ('yes' === $this->enabled)
		{
			add_action( 'woocommerce_thankyou_order_received_text', array(&$this, 'gokwik_cod_thankyou' ), 1,2 );
		}


	}

	public function process_admin_options(): bool {

		$process_admin_options =  parent::process_admin_options();
		if ('yes' !== $this->get_option( 'debug', 'no' ))
		{
			if (empty( self::$log ))
			{
				self::$log = wc_get_logger();
			}
			self::$log->clear( 'gokwik_cod' );
		}
		return $process_admin_options;
	}

//	public function gokwik_scripts(){
//
//	}


	/**
	 * @param $message
	 * @param string $level
	 */
	public static function log( $message, $level = 'info' )
	{
		if (self::$log_enabled)
		{
			if (empty( self::$log ))
			{
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'gokwik_cod' ) );
		}
	}

	/**
	 * Initializes Form Fields For Admin Dashboard
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title'       => 'Enable/Disable',
				'label'       => 'Enable Gokwik Gateway',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'yes'
			),
			'title' => array(
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'This controls the title which the user sees during checkout.',
				'default'     => 'Cash on Delivery - COD Powered by Gokwik ',
				'desc_tip'    => false,
			),
			'description' => array(
				'title'       => 'Description',
				'type'        => 'textarea',
				'description' => 'This controls the description which the user sees during checkout.',
				'default'     => 'Pay via cash during delivery. I agree to TnC by paying via Gokwik.',
			),
			'merchant_id' => array(
				'title'       => 'Merchant ID',
				'type'        => 'text',
				'description' => 'This Merchant ID provided by GoKwik team.',
			),
			'app_id' => array(
				'title' => __('APP ID', $this->id),
				'type' => 'text',
				'description' => __('The APP Id and APP secret given by Gokwik Admin. Use test or live for test or live mode.', $this->id)
			),
			'app_secret' => array(
				'title' => __('APP Secret', $this->id),
				'type' => 'text',
				'description' => __('The APP Id and APP secret given by Gokwik Admin. Use test or live for test or live mode.', $this->id)
			),
			'testmode' => array(
				'title'       => 'Test mode',
				'label'       => 'Enable Test Mode',
				'type'        => 'checkbox',
				'description' => 'Place the payment gateway in test mode using test Merchant ID.',
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'debug'                 => array(
				'title'       => __( 'Debug log', 'woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce' ),
				'default'     => 'no',
				/* translators: %s: URL */
				'description' => sprintf( __( 'Log Gokwik events, inside %s Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'woocommerce' ), '<code>' . WC_Log_Handler_File::get_log_file_path( 'gokwik' ) . '</code>' ),
			),
		);
	}

	public function create_order($order){
		global $woocommerce;
		$api = new App($this->app_id, $this->app_secret, "woocommerce");
		if ( $this->test_mode == 'yes'){
			App::enableTestMode();
		}
		//TODO:CHANGE REQUEST BODY - Done
		$result = get_order_data_for_gokwik($order->get_id(), 'cod');
		$result = $api->order->create($result);
		if ($result['statusCode'] == 200){
			if (in_array('gokwik_oid',array_keys($result['data'])) && in_array('request_id',array_keys($result['data']))){
				$order->add_order_note("Gokwik order_id: ".$result['data']['gokwik_oid']);
				$woocommerce->session->set("GOKWIK_OID",$result['data']['gokwik_oid']);
				$woocommerce->session->set("GOKWIK_REQ_ID", $result['data']['request_id']);
			}else{
				//Order already exits case
				$woocommerce->session->set("GOKWIK_OID",null);
				$woocommerce->session->set("GOKWIK_REQ_ID",null);
			}
		}
	}

	public function getCustomerDetail($order)
	{
		if (version_compare(WOOCOMMERCE_VERSION, '2.7.0', '>='))
		{
			$args = array(
				'name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'email'   => $order->get_billing_email(),
				'contact' => substr($order->get_billing_phone(), -10),
			);
		}
		else
		{
			$args = array(
				'name'    => $order->billing_first_name . ' ' . $order->billing_last_name,
				'email'   => $order->billing_email,
				'contact' => substr($order->billing_phone, -10),
			);
		}

		return $args;
	}


	public function gokwik_cod_thankyou( $str, $order )
	{


		if ($order && $this->id === $order->get_payment_method())
		{
			$orderData = $this->getCustomerDetail($order);
			$str = esc_html__( 'Thank you for your order please pay on delivery. Your transaction has been completed, and a receipt for your purchase has been emailed to you. Log into your Gokwik account to view transaction details.', 'woocommerce' );

			$str .=  '<form name="gokwik" action="merchant_handler" method="POST">
						   <input type="text" name="mid" value="'.$this->merchant_id.'" id="mid">
						   <input type="text" name="moid" value="'.$order->get_id().'" id="moid">
						   <input type="text" name="gokwik_oid" value="" id="gokwik_oid">
						   <input type="text" name="phone" value="'.substr($orderData['contact'], -10).'" id="phone">
						   <input type="text" name="total" value="'.$order->get_total().'" id="total">
						   <input type="text" name="order_type" value="cod" id="order_type">
						   <input type="text" name="action" value="checkout" id="action">
						   <input type="text" name="request_id" value="" id="request_id">
						   <input type="text" name="transaction_id" value="" id="transaction_id">
						   <input type="text" name="auth_token" value="" id="auth_token">
					</form>';
		}

		return $str;
	}

	public function process_payment( $order_id ): array {
		global $woocommerce;
		$order = wc_get_order( $order_id );
		if($order->get_total() > 0)
		{
			//TODO: add const WP_ORDER - Done
			$woocommerce->session->set(self::WP_ORDER, $order_id);

			$orderKey = $this->getOrderKey($order);

			$order_pay_method = get_post_meta( $order->get_id(), '_payment_method', true );
			if($order_pay_method=='cod')
			{
				$api = new App($this->app_id, $this->app_secret, "woocommerce");
				if($this->test_mode == 'yes'){
					App::enableTestMode();
				}
//				SERVER STATUS CALL
				try {
					$res = $api->plugin->health_check();
				}
				catch (Exception $e){
					$res = null;
				}
				if($res == null)
				{
					// Mark as processing or on-hold (payment won't be taken until delivery).
//					$order->update_status( apply_filters( 'woocommerce_cod_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ), __( 'Payment to be made upon delivery.', 'woocommerce' ) );
					// Remove cart.
					WC()->cart->empty_cart();
					// Return thankyou redirect.
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				}
				else
				{
					$this->create_order($order);
					//TODO:Order note  in save
					if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>='))
					{
						return array(
							'result' => 'success',
							'redirect' => add_query_arg('key', $orderKey, $order->get_checkout_order_received_url())
						);
					}
					else if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>='))
					{
						return array(
							'result' => 'success',
							'redirect' => add_query_arg('order', $order->get_id(),
								add_query_arg('key', $orderKey, $order->get_checkout_order_received_url()))
						);
					}
					else
					{
						return array(
							'result' => 'success',
							'redirect' => add_query_arg('order', $order->get_id(),
								add_query_arg('key', $orderKey, get_permalink(get_option('woocommerce_order_status_completed'))))
						);
					}
				}
			}
		}
		else{
			$order->payment_complete();
			// Remove cart.
			$woocommerce->cart->empty_cart();

			// Return thankyou redirect.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	return array(
		'result'   => 'success',
		'redirect' => $this->get_return_url( $order ),
	);
	}

	protected function getOrderKey($order)
	{
		$orderKey = null;

		if (version_compare(WOOCOMMERCE_VERSION, '3.0.0', '>='))
		{
			return $order->get_order_key();
		}

		return $order->order_key;
	}



}

new WC_Gateway_COD();