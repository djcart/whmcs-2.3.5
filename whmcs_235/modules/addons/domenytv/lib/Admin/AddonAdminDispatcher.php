<?php

namespace WHMCS\Module\Addon\domenytv\Admin;

use WHMCS\Module\Addon\domenytv\Admin\Controllers\Prices\PricesController;
use WHMCS\Module\Common\domenytv\Admin\AdminDispatcher;

/**
 * Admin dispatcher
 */
class AddonAdminDispatcher extends AdminDispatcher
{

    protected static $classPath = '\\WHMCS\\Module\\Addon\\domenytv\\Admin\\Controllers\\';

    public function dispatch($action, $parameters)
    {

        if (!$action) {
            $action = 'index';
        }

        $response = parent::dispatch($action, $parameters);

        if ($response) {
            return $response;
        }

        return $this->index($parameters);
    }

    public function index($parameters)
    {
        $controller = new PricesController();
        return $controller->priceUpdater($parameters);
    }
}
