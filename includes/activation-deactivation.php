<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-03:29 PM
 * Date-14-02-2021
 * File-activation-deactivation.php
 * Project-wp-gokwik-woocommerce
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

require __DIR__.'./../gokwik-php-sdk/Gokwik.php';
use Gokwik\Api\App;

register_activation_hook(GOKWIK_FILE, 'gokwik_activate');
function gokwik_activate(){
	$app = new App("", "", "");
	//TODO: remove testmode
	App::enableTestMode();
	$app->plugin->activate(get_home_url());
}

register_deactivation_hook(GOKWIK_FILE, 'gokwik_deactivate');
function gokwik_deactivate(){
	$app = new App("", "", "");
	//TODO: remove testmode
	App::enableTestMode();
	$app->plugin->deactivate(get_home_url());
}

register_uninstall_hook(GOKWIK_FILE, 'gokwik_uninstall');
function gokwik_uninstall(){
	$app = new App('', "", "");
	//TODO: remove testmode
	App::enableTestMode();
	$app->plugin->uninstall(get_home_url());
}