<?php
namespace WHMCS\Module\Addon\domenytv\Admin\Controllers\Installer;

use WHMCS\Module\Addon\domenytv\Installer;
use WHMCS\Module\Common\domenytv\Admin\Controllers\Controller;

class InstallerController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function installationError($vars)
    {
        $installer = new Installer();
        $check = $installer->check();

        $this->smarty->assign('installationCheck', $check);
        $view = $this->fetch('installer');

        return $view;
    }


    public function install() {

        $installer = new Installer();
        $response = $installer->install();

        if (isset($response['status']) && $response['status'] === 'success') {
            $this->smarty->assign('installationError', false);
        } else {
            $this->smarty->assign('installationError', true);
        }

        return $this->installationError([]);
    }
}
