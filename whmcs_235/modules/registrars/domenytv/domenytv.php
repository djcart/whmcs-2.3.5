<?php

/**
 * Domeny.tv - Registrar module for WHMCS
 *
 * @package   Domeny.tv registrars plugin
 * @author    MSERWIS
 * @copyright 2022 MSERWIS
 * @version   V2.35
 * @link      http://www.domeny.tv/reseller
 */

use WHMCS\Module\Registrar\domenytv\Admin\RegistrarAdminDispatcher;

require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'autoload.php');

function getDomenytvConfig()
{
    $config = array(
        'thisVersion' => 232,
        'dataFilePath' => '/modules/registrars/domenytv/data.json'
    );

    return $config;
}

function domenytv_getConfigArray()
{
    $configarray = array(
        "Username" => array("Type" => "text", "Size" => "20", "Description" => "Enter your username here"),
        "Password" => array("Type" => "password", "Size" => "20", "Description" => "Enter your password here"),
        "TestMode" => array(
            "FriendlyName" => "Test mode",
            "Type" => "yesno"
        ),
        "DisableTransferOut" => array(
            "FriendlyName" => "Disable transfer out",
            "Type" => "yesno"
        ),
        "ErrorsLang" => array(
            "FriendlyName" => "Errors language",
            "Type" => "dropdown",
            "Options" => "PL,EN",
            "Default" => "PL",
        ),
    );
    return $configarray;
}

function domenytv_RegisterDomain($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_registerDomain', $params);
}

function domenytv_GetDNS($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_getDNS', $params);
}

function domenytv_SaveDNS($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_saveDNS', $params);
}

function domenytv_GetEPPCode($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_getEPPCode', $params);
}

function domenytv_RenewDomain($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_renewDomain', $params);
}

function domenytv_GetNameservers($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_getNameservers', $params);
}

function domenytv_SaveNameservers($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_saveNameservers', $params);
}

function domenytv_RegisterNameserver($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_registerNameserver', $params);
}

function domenytv_ModifyNameserver($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_modifyNameserver', $params);
}

function domenytv_DeleteNameserver($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_deleteNameserver', $params);
}

function domenytv_TransferDomain($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_transferDomain', $params);
}

function domenytv_TransferSync($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_transferSync', $params);
}

function domenytv_Sync($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_sync', $params);
}

function domenytv_IDProtectToggle($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_IDProtectToggle', $params);
}

function domenytv_GetRegistrarLock($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_getRegistrarLock', $params);
}

function domenytv_SaveRegistrarLock($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_saveRegistrarLock', $params);
}

function domenytv_CheckAvailability($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_checkAvailability', $params);
}

function domenytv_GetContactDetails($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_getContactDetails', $params);
}

function domenytv_SaveContactDetails($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_saveContactDetails', $params);
}

function domenytv_GetDomainSuggestions($params)
{
    return (new RegistrarAdminDispatcher())->dispatch('Registrar_getDomainSuggestions', $params);
}