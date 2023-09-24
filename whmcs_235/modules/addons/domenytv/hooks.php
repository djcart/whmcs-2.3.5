<?php

require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'autoload.php');

use WHMCS\Module\Addon\domenytv\Admin\Controllers\Ssl\SslController;
use WHMCS\Module\Addon\domenytv\Installer;


add_hook('AfterCronJob', 1, function ($vars) {

    $installer = new Installer();

    if ($installer->check()) {
        $sslController = new SslController();
        $sslController->updateSslOrdersStatuses();
    }
});
