<?php

namespace WHMCS\Module\Common\domenytv\Admin\Controllers;

use \SoapFault;
use WHMCS\Module\Common\domenytv\Countries;
use WHMCS\Module\Common\domenytv\ApiResponse;

class ApiController
{

   private $login;
   private $password;
   private $url; // 'https://www.domeny.tv/regapi/soap.wsdl.xml';
   private $client;

   public function __construct($url, $login, $password)
   {
      $this->url = $url;
      $this->login = $login;
      $this->password = $password;
      $this->client = new \SoapClient(
         $this->url,
         array(
            array(
               'verifypeer' => false,
               'verifyhost' => false,
               'trace' => 1,
               'stream_context' => stream_context_create(
                  array(
                     'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                     )
                  )
               )
            )
         )
      );
   }

   /**
    * __call
    *
    * @param  mixed $action
    * @param  mixed $data
    *
    * @return ApiResponse
    */

   public function __call($action, $params)
   {
      return $this->send($action, $params[0]);
   }

   /**
    * Send API request
    *
    * @param  string $action
    * @param  array $data
    *
    * @return ApiResponse
    */

   private function send($action, $data = array())
   {
      $data_log = $data;

      $data['login'] = $this->login;
      $data['password'] = $this->password;

      if (function_exists('getDomenytvConfig')) {
         $config = getDomenytvConfig();
         $data['whmcsPlugin'] = $config['thisVersion'];
      }

      try {
         $response = new ApiResponse($this->client->$action($data));
      } catch (SoapFault $fault) {
         $response = new ApiResponse($fault);
      }

      $replacevars = array();

      if (function_exists('logModuleCall')) {
         logModuleCall('domenytv', $action, $data_log, $response, $response, $replacevars);
      }

      return $response;
   }

   /**
    * Send cached API request
    *
    * @param  string $action
    * @param  array $data
    * @param  int $validTime
    *
    * @return ApiResponse
    */

   private function sendCached($action, $data = array(), $validTime = 0)
   {
      $commonDirectory = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
      $cacheDirectory = $commonDirectory . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $action;

      if (!file_exists($cacheDirectory)) {
         \mkdir($cacheDirectory, 0600, true);
      }

      $md5 = md5($action . json_encode($data));
      $cacheFile = $cacheDirectory . DIRECTORY_SEPARATOR . $md5 . '.txt';
      $fileDate = @filemtime($cacheFile);

      if ($fileDate && $fileDate + $validTime * 60 > time()) {
         $replacevars = array();
         $response = unserialize(file_get_contents($cacheFile));
         if (function_exists('logModuleCall')) {
            logModuleCall('domenytv', $action, $data, $response, $response, $replacevars);
         }
         return $response;
      }

      $response = $this->send($action, $data);

      if (!$response->error() && $validTime > 0) {
         \file_put_contents($cacheFile, serialize($response));
         clearstatcache();
      }

      return $response;
   }

   /*private function response($response)
   {
      $response['r_status'] = 1;
      $response['result_msg'] = null;

      if (!isset($response['result'])) {
         return $response;
      }

      $response_msg = Errors::getByCode($response['result'], AdminDispatcher::$lang);

      if ($response['result'] != 1000 && $response['result'] != 1001) {
         $response['r_status'] = 0;
         $response['error_no'] = $response['result'];
         $response['error_msg'] = $response_msg;
         $response['error'] = $response['result'] . ' - ' . $response['error_msg'];
      }

      if ($response['result'] != 1000) {
         $response['result_msg'] = $response_msg;
      }

      return $response;
   }*/

   public function getDomainsPrices()
   {
      $response = $this->send('pricelist');
      return $response;
   }

   public function getAccountBalance()
   {
      $response = $this->send('accountBalance');
      return $response;
   }

   public function getAllDomains($forceCache = false)
   {
      $response = $this->sendCached('getAllDomains', array(), $forceCache ? 0 : 5);
      return $response;
   }

   public static function getSslTypes()
   {
      $sslTypes = array(
         'rapidssl' => 'RapidSSL',
         'rapidssl_wildcard' => 'RapidSSL wildcard',
         'geotrust_quicksslpremium' => 'GeoTrust QuickSSL Premium',
         'geotrust_trueid' => 'GeoTrust True BusinessID',
         'geotrust_ev' => 'GeoTrust True BusinessID EV',
         'geotrust_wildcard' => 'GeoTrust True BusinessID Wildcard',
         'symantec_secure' => 'Symantec Secure Site',
         'symantec_securepro' => 'Symantec Secure Site Pro',
         'symantec_ev' => 'Symantec Secure Site EV',
         'symantec_evpro' => 'Symantec Secure Site Pro EV',
         'thawte_ssl123' => 'Thawte SSL123',
         'thawte_sslwebserver' => 'Thawte SSL Web Server',
         'thawte_wildcard' => 'Thawte Wildcard SSL',
         'thawte_ev' => 'Thawte SSL Web Server EV',
         'comodo_positive' => 'Comodo Positive SSL',
         'comodo_instant' => 'Comodo Instant OV',
         'comodo_ev' => 'Comodo EV',
         'comodo_wildcard' => 'Comodo Positive SSL Wildcard',
         'comodo_wildcard_premium' => 'Comodo Premium Wildcard',
      );

      return $sslTypes;
   }

   public function generateCSR($countryName, $state, $city, $organization, $unitName, $name, $email, $key)
   {
      $dn = array(
         'countryName' => $countryName,
         'stateOrProvinceName' => $state,
         'localityName' => $city,
         'organizationName' => $organization,
         'organizationalUnitName' => $unitName,
         'commonName' => $name,
         'emailAddress' => $email
      );

      $algo = 'sha256';
      $config = array(
         'private_key_type' => OPENSSL_KEYTYPE_RSA,
         'private_key_bits' => $key,
         'digest_alg' => $algo
      );

      $privkey = openssl_pkey_new($config);
      $csr = openssl_csr_new($dn, $privkey);

      $returnArray = array(
         'private_key' => null,
         'csr' => null
      );

      openssl_pkey_export($privkey, $returnArray['private_key']);
      openssl_csr_export($csr, $returnArray['csr']);

      return $returnArray;
   }

   public static function getCountries()
   {
      return Countries::get();
   }
}
