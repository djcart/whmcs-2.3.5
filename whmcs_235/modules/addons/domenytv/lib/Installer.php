<?php
namespace WHMCS\Module\Addon\domenytv;

use WHMCS\Module\Addon\domenytv\Admin\Controllers\Ssl\SslController;
use WHMCS\Database\Capsule;

class Installer
{
    public function install() {
        $sslController = new SslController(false);
        return $sslController->install();
    }

    public function check() {
        if (!Capsule::schema()->hasTable('dtv_ssl_orders'))
        {
            return false;
        }

        return true;
    }
}
