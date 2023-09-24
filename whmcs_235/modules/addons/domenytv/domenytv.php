<?php
/**
 * Domeny.tv - Addon module for WHMCS
 *
 * @package   Domeny.tv addon module
 * @author    MSERWIS
 * @copyright 2022 MSERWIS
 * @version   V2.35
 * @link      http://www.domeny.tv/reseller
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'autoload.php');

use WHMCS\Module\Addon\domenytv\Admin\AddonAdminDispatcher as AdminDispatcher;
use WHMCS\Module\Common\domenytv\Lang;

use WHMCS\Module\Addon\domenytv\Installer;

function domenytv_config()
{
    return array(
        'name' => 'Domeny.tv',
        'description' => 'Moduł Domeny.tv',
        'author' => 'Mserwis.pl',
        'language' => 'polish',
        'version' => '2.35',
        'fields' => array(
            'api_login' => array(
                'FriendlyName' => 'Login',
                'Type' => 'text',
                'Size' => '25',
                'Description' => 'Identyfikator używany do logowania do API produkcyjnego',
            ),
            'api_password' => array(
                'FriendlyName' => 'Hasło',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Hasło używane do logowania do API produkcyjnego',
            ),
        ),
    );

}

function domenytv_activate()
{
    $installer = new Installer();
    return $installer->install();
}

function domenytv_deactivate()
{

    return array(
        'status' => 'success',
        'description' => 'Moduł domeny.tv został wyłączony - dane nie zostały usunięte.',
    );
}

function domenytv_upgrade($vars)
{
    $currentlyInstalledVersion = $vars['version'];

    if ($currentlyInstalledVersion < '2.25') {

        $installer = new Installer();
        return $installer->install();
    }

}

function domenytv_output($vars)
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $dispatcher = new AdminDispatcher();

    $lang = Lang::instance();
    $lang->loadLangArray($vars['_lang']);

     //checking tables
     $installer = new Installer();
     if (!$installer->check()) {

        $actionExplode = explode('_', $action);

        if ($actionExplode[0] != 'installer') {
            $action = 'installer_installationError';
        }
     }

    $response = $dispatcher->dispatch($action, $vars);

    echo $response;
}

function domenytv_sidebar($vars)
{
    $lang = Lang::instance();
    $lang->loadLangArray($vars['_lang']);

    $sidebar =
        '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16"> Domeny.tv</span>
        <ul class="menu">
         <li><a href="addonmodules.php?module=domenytv"><img src="images/icons/income.png" class="absmiddle" width="16" height="16"> Aktualizator cen' . '</a></li>
         <li><a href="addonmodules.php?module=domenytv#/certs/order"><img src="images/icons/ordersadd.png"  class="absmiddle" width="16" height="16"> SSL - nowe zamówienie' . '</a></li>
         <li><a href="addonmodules.php?module=domenytv#/certs/orders"><img src="images/icons/orders.png" class="absmiddle" width="16" height="16"> SSL - zamówienia' . '</a></li>
       </ul>
       ';

    return $sidebar;
}
