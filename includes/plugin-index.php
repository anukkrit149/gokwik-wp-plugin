<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-11:32 PM
 * Date-13-02-2021
 * File-plugin-index.php
 * Project-wp-gokwik-woocommerce
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

require __DIR__.'./../gokwik-php-sdk/Gokwik.php';
//require __DIR__.'/utility.php';
use Gokwik\Api\App;

//TODO:ONLY FOR Only UPI Case - verify payment
//add_action('woocommerce_order_status_changed','order_status_change', 10, 3);
//function order_status_change($order_id,$old_status,$new_status){
//	if ( ! $order_id ) { return;}
//	global $woocommerce;
//
//
//}

/**
 * Merchant WEBHOOK,
 * we hit from orders.notify to change order status
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'gokwik-checkout/v1', '/gk-callback/', array(
		'methods' => 'POST',
		'callback' => 'kwikStatusWebhook',
	) );
} );
function kwikStatusWebhook()
{
	header('Content-Type: application/json');

	if (!isset($_POST['key']))
	{
		$errMsg = array('status' => 'failed', 'message' => 'key not define.');
		echo json_encode($errMsg);
		exit;
	}
	if (!isset($_POST['moid']))
	{
		$errMsg = array('status' => 'failed', 'message' => 'moid not define.');
		echo json_encode($errMsg);
		exit;
	}
	if (!isset($_POST['method']))
	{
		$errMsg = array('status' => 'failed', 'message' => 'method not define.');
		echo json_encode($errMsg);
		exit;
	}

	$apikey = $_POST['key'];
	$moid = $_POST['moid'];
	$method = $_POST['method'];

	if(!empty($apikey) && isset($apikey) && !empty($moid) && isset($moid) && !empty($method) && isset($method))
	{
		$flag = false;
//		$gokwikObjUPI = new WC_Gokwik_Gateway_UPI();
		$gokwikObjCOD = new WC_Gateway_COD();
//		if(($gokwikObjUPI->enabled=='yes') && !empty($gokwikObjUPI->app_secret) && isset($gokwikObjUPI->app_secret))
//		{
//			if($apikey !== $gokwikObjUPI->app_secret)
//			{
//				$errMsg = array('status' => 'failed', 'message' => 'Invalid Secret UPI');
//				echo json_encode($errMsg);
//				exit;
//			}
//			$flag = true;
//		}
		if (($gokwikObjCOD->enabled=='yes') && !empty($gokwikObjCOD->app_secret) && isset($gokwikObjCOD->app_secret))
		{
			if($apikey !== $gokwikObjCOD->app_secret)
			{
				$errMsg = array('status' => 'failed', 'message' => 'Invalid Secret COD');
				echo json_encode($errMsg, JSON_PRETTY_PRINT);
				exit;
			}
			$flag = true;
		}

		if($flag)
		{
			global $woocommerce;
			$order = wc_get_order( $moid );
			if(!empty($order))
			{
				if($method=='update')
				{
					if (!isset($_POST['oid']))
					{
						$errMsg = array('status' => 'failed', 'message' => 'oid not define.');
						echo json_encode($errMsg);
						exit;
					}
					if (!isset($_POST['status']))
					{
						$errMsg = array('status' => 'failed', 'message' => 'status not define.');
						echo json_encode($errMsg);
						exit;
					}
					$kwikoid = $_POST['oid'];
					$status = $_POST['status'];
					if(!empty($kwikoid) && isset($kwikoid) && !empty($status) && isset($status))
					{
						$order_pay_method = get_post_meta( $order->get_id(), '_payment_method', true );
						$old_status = $order->get_status();
						if($order_pay_method == 'upi' || $order_pay_method == 'cod')
						{
							if ( $order->get_status() == 'pending' || $order->get_status() == 'processing' || $order->get_status() == 'cancelled' || $order->get_status() == 'failed') {
								if($order->get_status()!=$status)
								{
									$order->update_status($status);
									$order->add_order_note("Gokwik OrderId: ".$kwikoid);

									if ($order->needs_payment() === false)
									{
										if (isset($woocommerce->cart) === true)
										{
											$woocommerce->cart->empty_cart();
										}
									}

									echo json_encode(array('status' => 'success', 'old_status' => $old_status, 'new_status' => $order->get_status(), 'message' => 'Status Updated. Old Status was '.$old_status. ' and New status is '.$order->get_status()));
									exit;
								}
								else
								{
									$errMsg = array('status' => 'failed', 'message' => 'For Update Status need to be different from current status '.$order->get_status());
									echo json_encode($errMsg);
									exit;
								}
							}
							else{
								echo json_encode(array('status' => 'failed', 'message' => 'Already Status Updated Now current status is '.$order->get_status()));
								exit;
							}
						}
						else
						{
							$errMsg = array('status' => 'failed', 'message' => 'Payment method changed for this OrderId Now new method is '.$order_pay_method);
							echo json_encode($errMsg);
							exit;
						}
					}
					else{
						$errMsg = array('status' => 'failed', 'message' => 'For Update Gokwik Order ID and Status is required.');
						echo json_encode($errMsg);
						exit;
					}
				}
				elseif($method=='post')
				{
					$result = get_order_data_for_gokwik($moid, 'cod');
					$res = array('status' => 'success', 'data' => $result, 'message' => 'Order data search completed.');
					echo json_encode($res);
					exit;
				}
				else
				{
					$errMsg = array('status' => 'failed', 'message' => 'Wrong method.');
					echo json_encode($errMsg);
					exit;
				}
			}
			else
			{
				$errMsg = array('status' => 'failed', 'message' => 'Invalid order number.');
				echo json_encode($errMsg);
				exit;
			}
		}
		else
		{
			$errMsg = array('status' => 'failed', 'message' => 'Gokwik secret key not present or invalid.');
			echo json_encode($errMsg);
			exit;
		}
	}
	else
	{
		$errMsg = array('status' => 'failed', 'message' => 'Gokwik plugin not active or key|moid|method not not given.');
		echo json_encode($errMsg);
		exit;
	}
}

add_action( 'wp_head', 'prefetchDNS', 0);
/**
 *
 */
function prefetchDNS()
{
	if(is_checkout())
	{
		$dnsmode = false;
//		$gokwikObjUPI = new WC_Gokwik_Gateway_UPI();
		$gokwikObjCOD = new WC_Gateway_COD();
//		if(($gokwikObjUPI->enabled=='yes') && !empty($gokwikObjUPI->testmode) && isset($gokwikObjUPI->testmode))
//		{
//			if($gokwikObjUPI->testmode)
//			{
//				$dnsmode = true;
//			}
//		}
		if (($gokwikObjCOD->enabled=='yes') && isset($gokwikObjCOD->testmode))
		{
			if($gokwikObjCOD->testmode)
			{
				$dnsmode = true;
			}
		}

		if ($dnsmode)
		{
			echo "<link rel='dns-prefetch' href='".get_home_url()."' />";
			echo "<link rel='dns-prefetch' href='//devapi.gokwik.co' />";
			echo "<link rel='dns-prefetch' href='//devcdn.gokwik.co' />";
		}
		else
		{
			echo "<link rel='dns-prefetch' href='".get_home_url()."' />";
			echo "<link rel='dns-prefetch' href='//api.gokwik.co' />";
			echo "<link rel='dns-prefetch' href='//cdn.gokwik.co' />";
		}
	}
}



