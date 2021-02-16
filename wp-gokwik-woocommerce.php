<?php

/*
Plugin Name: Gokwik Payment Gateway
Plugin URI: https://www.gokwik.co/docs/woocommerce-plugin
Description: GoKwik partners with e-commerce websites to provide a seamless shopping experience to millions of e-commerce consumers.
Version: 2.2.0
Author: Team Gokwik
Author URI: https://www.gokwik.co/
Text Domain: WooCommerce-Gokwik-Checkout
Copyright: © 2021 WooCommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

define('WOO_PAYMENT_DIR', plugin_dir_path(__FILE__));
define('GOKWIK_FILE', __FILE__);

require __DIR__.'/includes/activation-deactivation.php';

/*
 * Check for WooCommerce is active
 * */
if (! in_array('woocommerce/woocommerce.php',apply_filters('active_plugins',get_option('active_plugins'))))
	return;

$plugin = plugin_basename(__FILE__);


add_action('plugins_loaded', 'gokwik_init_gateway', 0);
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'gokwik_settings_link');

function gokwik_settings_link($links): array {
	$plugins_links = array(
//		'<a href="admin.php?page=wc-settings&tab=checkout&section=upi">' . __( 'UPI Settings', 'woocommerce-gokwik' ) . '</a>',
		'<a href="admin.php?page=wc-settings&tab=checkout&section=cod">' . __( 'COD Settings', 'woocommerce-gokwik' ) . '</a>',
		'<a href="https://docs.woocommerce.com/">' . __( 'Support', 'woocommerce-gokwik' ) . '</a>',
		'<a href="https://docs.woocommerce.com/document/gokwik/">' . __( 'Docs', 'woocommerce-gokwik' ) . '</a>',
	);
	return array_merge($plugins_links, $links);
}

function gokwik_init_gateway(){
	require_once 'includes/plugin-index.php';
	require_once 'includes/WC_Gateway_COD.php';
//	require_once 'includes/WC_Gokwik_Gateway_UPI.php';

//	if (!class_exists('WC_Gokwik_Gateway_UPI'))
//		return;

	add_filter('woocommerce_payment_gateways', 'gokwik_add_gateways');
	function gokwik_add_gateways($gateways){
//		$gateways[] = 'WC_Gokwik_Gateway_UPI';
		$gateways[] = 'WC_Gateway_COD';
		return $gateways;
	}
}
