<?php /** @noinspection ALL */
/********************
 * Developed by Anukkrit Shanker
 * Time-11:32 PM
 * Date-13-02-2021
 * File-utility.php
 * Project-wp-gokwik-woocommerce
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/
global $woocommerce;
function get_order_notes_for_anukkrit($order_id)
{
	$args = array (
		'post_id' 	=> $order_id,
		'orderby' 	=> 'comment_ID',
		'order' 	=> 'DESC',
		'approve' 	=> 'approve',
		'type' 		=> 'order_note'
	);
	remove_filter ( 'comments_clauses', array (
		'WC_Comments',
		'exclude_order_comments'
	), 10, 1 );

	$notes = get_comments ( $args );
	return $notes;
}



function get_order_data_for_gokwik($order_id, $method_id)
{
	$order = new WC_Order($order_id);
	$line_items = array();
	foreach ( $order->get_items() as $key => $item ) {
		//TODO:Why??????????? it will be null already if it is not valid
		//################################################
		$product = wc_get_product($item->get_product_id());

		$product_id = null;
		// Check if the product exists.
		if (is_object($product)) {
			$product_id = $product->get_id();
		}
		//##################################################

		$items = array(
			'product_id' => $item->get_product_id(),
			'variation_id' => $item->get_variation_id(),
			'product' => $item->get_product(),
			'name' => $item->get_name(),
			'sku' => $product->get_sku(),
			'price' => $order->get_item_total($item, false, false),
			'quantity' => $item->get_quantity(),
			'subtotal' => $item->get_subtotal(),
			'total' => $item->get_total(),
			'tax' => $item->get_subtotal_tax(),
			'taxclass' => $item->get_tax_class(),
			'taxstat' => $item->get_tax_status(),
			'allmeta' => $item->get_meta_data(),
			'somemeta' => $item->get_meta( '_whatever', true ),
			'type' => $item->get_type(),
			'product_url' => get_permalink($product_id),
			'product_thumbnail_url' => wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'thumbnail', TRUE)[0]
		);
		$line_items[] = $items;
	}

	//TODO: Cross check coupon per item
	$coupon_lines = array();
	foreach ($order->get_items('coupon') as $coupon_item_id => $coupon_item) {

		$coupon_lines[] = array(
			'id' => $coupon_item_id,
			'code' => $coupon_item['name'],
			'amount' => $coupon_item['discount_amount'],
		);
	}

	$customer      = new WC_Customer( $order->get_user_id() );

	$notes = get_order_notes($order->get_id());
	$order_notes = array();
	if($notes)
	{
		foreach( $notes as $note )
		{
			$order_notes[] = array(
				'note' => wptexturize (wp_kses_post($note->comment_content )),
				'author' => $note->comment_author,
				'date' => $note->comment_date,
				'date_gmt' => $note->comment_date_gmt
			);
		}
	}

	$data = array("order" => array("id" => $order->get_id(),
	                               "status" => $order->get_status(),
	                               "total" => $order->get_total(),
	                               "subtotal" => $order->get_subtotal(),
	                               "total_line_items" => count($order->get_items()),
	                               "total_line_items_quantity" => $order->get_item_count(),
	                               'total_tax' => $order->get_total_tax(),
	                               "total_shipping" => $order->get_total_shipping(),
	                               "total_discount" => $order->get_discount_total(),
	                               "payment_details" => array(
		                               "method_id" => $method_id,
	                               ),
	                               "billing_address" => array(
		                               "first_name" => $order->get_billing_first_name(),
		                               "last_name" => $order->get_billing_last_name(),
		                               "company" => $order->get_billing_company(),
		                               "address_1" => $order->get_billing_address_1(),
		                               "address_2" => $order->get_billing_address_2(),
		                               "city" => $order->get_billing_city(),
		                               "state" => $order->get_billing_state(),
		                               "postcode" => $order->get_billing_postcode(),
		                               "country" => $order->get_billing_country(),
		                               "email" => $order->get_billing_email(),
		                               "phone" => substr($order->get_billing_phone(), -10)
	                               ),
	                               "shipping_address" => array(
		                               "first_name" => $order->get_shipping_first_name(),
		                               "last_name" => $order->get_shipping_last_name(),
		                               "company" => $order->get_shipping_company(),
		                               "address_1" => $order->get_shipping_address_1(),
		                               "address_2" => $order->get_shipping_address_2(),
		                               "city" => $order->get_shipping_city(),
		                               "state" => $order->get_shipping_state(),
		                               "postcode" => $order->get_shipping_postcode(),
		                               "country" => $order->get_shipping_country()
	                               ),
	                               "customer_ip" => $order->get_customer_ip_address(),
	                               "customer_user_agent" => $order->get_customer_user_agent(),
	                               "customer_id" => $order->get_user_id(),
	                               "line_items" => json_encode($line_items),
	                               "promo_code" => json_encode($coupon_lines),
	                               "source" => "woocommerce",
                                   "order_notes" => json_encode($order_notes) ) );
	return $data;
}

