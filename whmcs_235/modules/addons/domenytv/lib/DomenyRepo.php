<?php
namespace WHMCS\Module\Addon\domenytv;

class DomenyTvApi
{

    private $api;

    public function __construct($apiController)
    {
        $this->api = $apiController;
    }

    public function sslVerifyOrder($data)
    {
        $response = $this->api->send('sslVerifyOrder', $data);
        return $response;
    }

    public function sslPlaceOrder($data)
    {
        $response = $this->api->send('sslPlaceOrder', $data);
        return $response;
    }

    public function sslCheckOrder($data)
    {
        $response = $this->api->send('sslCheckOrder', $data);
        return $response;
    }

    public function getDomainsPrices($data)
    {
        $response = $this->api->send('pricelist');
        return $response;
    }
}
