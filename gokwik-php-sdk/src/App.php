<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-01:57 AM
 * Date-05-02-2021
 * File-Api.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 *
 * Working-
 *********************/


namespace Gokwik\Api;


class App
{
    protected static $baseUrl = "https://api.gokwik.co/v1/";
    protected static $app_id = null;
    protected static $app_secret = null;
    protected static $merchant_Name = null;
    protected static $test_mode = false;
    public $order = null;
    public $payment = null;
    public $plugin = null;
    public static $appsDetails = null;


    public static function enableTestMode()
    {
        self::$test_mode = true;
        self::$baseUrl = "https://devapi.gokwik.co/v1/";
    }


    public static function disableTestMode()
    {
        self::$test_mode = false;
        self::$baseUrl = "https://api.gokwik.co/v1/";
    }



    const VERSION = '1.0.0';

    /**
     * @return null
     */
    public static function getMerchantName()
    {
        return self::$merchant_Name;
    }


    /**
     * Api constructor.
     * @param $app_id
     * @param $app_secret
     * @param $merchant_Name
     */
    public function __construct($app_id, $app_secret, $merchant_Name){
        self::$app_id = $app_id;
        self::$app_secret = $app_secret;
        self::$merchant_Name = $merchant_Name;
        $this->order = new Order();
        $this->payment = new Payment();
        $this->plugin = new Plugin();
    }

    /**
     * @return string
     */
    public static function getBaseUrl(){
        return self::$baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public static function setBaseUrl($baseUrl){
        self::$baseUrl = $baseUrl;
    }

    /**
     * @return null
     */
    public static function getAppId(){
        return self::$app_id;
    }

    public function setAppDetails($title, $version = null)
    {
        $app = array(
            'title' => $title,
            'version' => $version
        );

        array_push(self::$appsDetails, $app);
    }

    /**
     * @param null $app_id
     */
    public static function setAppId($app_id){
        self::$app_id = $app_id;
    }

    /**
     * @return null
     */
    public static function getAppSecret(){
        return self::$app_secret;
    }

    public function getAppsDetails()
    {
        return self::$appsDetails;
    }

    /**
     * @param null $app_secret
     */
    public static function setAppSecret($app_secret){
        self::$app_secret = $app_secret;
    }


    public static function getFullUrl($relativeUrl){
        return self::getBaseUrl() . $relativeUrl;
    }

}
