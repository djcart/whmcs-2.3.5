<?php
namespace WHMCS\Module\Addon\domenytv\Admin\Controllers\Prices;

use WHMCS\Module\Addon\domenytv\Admin\Controllers\AjxController;

class PricesApiController extends AjxController
{

    private $currencyId;
    private $priceUpdater;

    public function __construct()
    {
        parent::__construct();
        $this->currencyId = isset($_REQUEST['currencyId']) ? $_REQUEST['currencyId'] : null;

        if (!$this->currencyId) {
            $this->response->respondWithError('WHMCS: brak zdefiniowanych walut dla płatności');
        }

        $this->priceUpdater = new PricesUpdater();
        $this->priceUpdater->setCurrencyId($this->currencyId);
    }

    public function getPrices()
    {
        $pricesMatrix = $this->priceUpdater->getPricesMatrix();
        $this->response->respond($pricesMatrix);
    }


    public function getCurrencies() {

        $currencies = $this->priceUpdater->getCurrencies();

        $this->response->respond($currencies);
    }

    public function savePrices()
    {
        $prices = $this->getJsonPost();

        foreach ($prices as $tldPrice) {
            $this->priceUpdater->updateExtension($tldPrice);
        }
        $this->response->respond();
    }

    public function savePrice()
    {
        $extPrices = $this->getJsonPost();

        $this->priceUpdater->updateExtension($extPrices);
        $this->response->respond();
    }

}
