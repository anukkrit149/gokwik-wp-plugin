<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-01:15 AM
 * Date-08-02-2021
 * File-Request.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/


namespace Gokwik\Api;

use Requests;
use Exception;
use Requests_Hooks;
use Gokwik\Api\Errors;
use Gokwik\Api\Errors\ErrorCode;


// Available since PHP 5.5.19 and 5.6.3
// https://git.io/fAMVS | https://secure.php.net/manual/en/curl.constants.php
if (defined('CURL_SSLVERSION_TLSv1_1') === false)
{
    define('CURL_SSLVERSION_TLSv1_1', 5);
}

/**
 * Request class to communicate to the request libarary
 */
class Request
{
    /**
     * Headers to be sent with every http request to the API
     * @var array
     */
    protected static $headers = null;

    /**
     * @param array $headers
     */
    public static function setHeaders($headers)
    {
        self::$headers = $headers;
    }


    /**
     * @param $method
     * @param $url
     * @param array $data
     * @return mixed
     * @throws \Requests_Exception
     */
    public function request($method, $url, $data = array())
    {
        $url = App::getFullUrl($url);

        $hooks = new Requests_Hooks();

        $hooks->register('curl.before_send', array($this, 'setCurlSslOpts'));

        self::setHeaders(Array(
            'appid' => App::getAppId(),
            'appsecret' => App::getAppSecret(),
            'Content-Type' => 'application/json'
        ));

        $options = array(
            'hook' => $hooks,
            'timeout' => 60,
        );

        $headers = $this->getRequestHeaders();

//        echo $url;
//        echo "\n";

        $response = Requests::request($url, $headers, $data, $method, $options);
//        var_dump($response->body);
//        exit();

        $this->checkErrors($response);

        return json_decode($response->body, true);
    }

    public function setCurlSslOpts($curl)
    {
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
    }

    /**
     * Adds an additional header to all API requests
     * @param string $key   Header key
     * @param string $value Header value
     * @return null
     */
    public static function addHeader($key, $value)
    {
        self::$headers[$key] = $value;
    }

    /**
     * Returns all headers attached so far
     * @return array headers
     */
    public static function getHeaders()
    {
        return self::$headers;
    }


    protected function checkErrors($response)
    {
        $body = $response->body;
//        var_dump($body);
        $httpStatusCode = $response->status_code;

        try{
//           var_dump($response);
            $body = json_decode($response->body, true);
        }
        catch (Exception $e)
        {
            $this->throwServerError($body, $httpStatusCode);
        }

        if (($httpStatusCode < 200) or
            ($httpStatusCode >= 300))
        {
            $this->processError($body, $httpStatusCode, $response);
        }
    }

    protected function processError($body, $httpStatusCode, $response)
    {
        $this->verifyErrorFormat($body, $httpStatusCode);

        $code = $body['error']['code'];

        // We are basically converting the error code to the Error class name
        // Replace underscores with space
        // Lowercase the words, capitalize first letter of each word
        // Remove spaces
        $error = str_replace('_', ' ', $code);
        $error = ucwords(strtolower($error));
        $error = str_replace(' ', '', $error);

        // Add namespace
        // This is the fully qualified error class name
        $error = __NAMESPACE__.'\Errors\\' . $error;

        $description = $body['error']['description'];

        $field = null;
        if (isset($body['error']['field']))
        {
            $field = $body['error']['field'];

            // Create an instance of the error and then throw it
            throw new $error($description, $code, $httpStatusCode, $field);
        }

        throw new $error($description, $code, $httpStatusCode);
    }

    protected function throwServerError($body, $httpStatusCode)
    {
        $description = "The server did not send back a well-formed response. " . PHP_EOL .
            "Server Response: $body";

        throw new Errors\ServerError(
            $description,
            ErrorCode::SERVER_ERROR,
            $httpStatusCode);
    }

    protected function getRequestHeaders()
    {
        $uaHeader = array(
            'User-Agent' => $this->constructUa()
        );

        $headers = array_merge(self::$headers, $uaHeader);

        return $headers;
    }

    protected function constructUa()
    {
        $ua = 'Gokwik/v1-PHPSDK/' . App::VERSION . '/Merchant-'.App::getMerchantName().'/PHP/' . phpversion();

//        $ua .= ' ' . $this->getAppDetailsUa();

        return $ua;
    }



    protected function verifyErrorFormat($body, $httpStatusCode)
    {
        if (is_array($body) === false)
        {
            $this->throwServerError($body, $httpStatusCode);
        }

        if ((isset($body['error']) === false) or
            (isset($body['error']['code']) === false))
        {
            $this->throwServerError($body, $httpStatusCode);
        }

        $code = $body['error']['code'];

        if (Errors\ErrorCode::exists($code) === false)
        {
            $this->throwServerError($body, $httpStatusCode);
        }
    }
}


