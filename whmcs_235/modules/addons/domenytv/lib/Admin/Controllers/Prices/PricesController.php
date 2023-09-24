<?php
namespace WHMCS\Module\Addon\domenytv\Admin\Controllers\Prices;

use WHMCS\Module\Common\domenytv\Admin\Controllers\Controller;

class PricesController extends Controller
{
    public function priceUpdater($vars)
    {
        return $this->fetch('price_updater');
    }

}
