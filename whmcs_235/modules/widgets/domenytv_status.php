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

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

class DomenyTvStatusWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Domeny.tv - status';

    protected $description = 'Wyświetla czy są dostępne nowe aktualizacje modułów udostępnionych przez Domeny.tv';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 30;
    protected $requiredPermission = '';
    protected $showWrapper = true;

    private $path = '/modules/registrars/domenytv/data.json';
    private $installationDir = null;

    public function __construct()
    {
        if (!isset($CONFIG['SystemURL']) && isset($_SERVER["REQUEST_URI"])) {
            $this->installationDir = dirname(dirname($_SERVER["REQUEST_URI"]));
        } else {
            $this->installationDir = $CONFIG['SystemURL'];
        }
    }

    private function getCurrentVersion(){
        $data = file_get_contents('http://domeny.tv/files/whmcs.json');

        if ($data === false){
            return null;
        }

        $jsonData = json_decode($data);

        return $jsonData;
    }

    private function getThisVersion(){

        if (!class_exists('\WHMCS\Module\Registrar\Domenytv\DtvRegistrar')) {
            return '000';
        }

       return \WHMCS\Module\Registrar\Domenytv\DtvRegistrar::getVersion();
    }

    private function getBalance() {

        if (!class_exists('\WHMCS\Module\Addon\domenytv\Api\ApiController')) {
            return false;
        }

        $settings = $this->getDtvRegistrarSettings();

        if (!$settings) {
            return false;
        }

        $url = 'https://www.domeny.tv/regapi/soap.wsdl.xml';
        if ($settings['TestMode'] == 'on') {
            $url = 'https://www.domeny.tv/regapi/test/soap.wsdl.xml';
        }

        $api = new WHMCS\Module\Addon\domenytv\Api\ApiController($url, $settings['Username'],$settings['Password']);

        if (!method_exists($api, 'getAccountBalance')) {
            return false;
        }

        $result = $api->getAccountBalance();

        if ($result['result'] == 1000) {
            return $result['balance'].' PLN';
        }

        return false;
    }

    private function getDtvRegistrarSettings(){
        $configArray = array();

        $regData = Capsule::table('tblregistrars')->where('registrar', 'domenytv')->get();
        foreach($regData as $data) {
            $command = 'DecryptPassword';
            $postData = array(
                'password2' =>  $data->value,
            );

            $results = localAPI($command, $postData);
            if ($results['result'] == 'success') {
                $configArray[$data->setting] = $results['password'];
            }
        }

        if (!isset($configArray['TestMode']) || !isset($configArray['Password']) || !isset($configArray['Username'])) {
            return false;
        }

        return $configArray;
    }

    public function getBalanceHtml() {
        $balance = $this->getBalance();
        if ($balance == false) {
            return '';
        }

        $html = '<div class="row">
            <div class="col-sm-12 text-center">
                <div class="item">
                    <div class="icon-holder text-center">
                        <i class="pe-7s-piggy color-pink"></i>
                    </div>
                    <div class="data">
                        <div class="note">
                            <b>Twoje saldo:</b>
                        </div>
                        <div class="number color-pink">
                        '.$balance.'
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        return $html;
    }
    public function getData()
    {
        $returnData = new \StdClass();
        $current =  $this->getCurrentVersion();

        $returnData->currentVersion = 'Błąd połączenia';
        $returnData->currentDate = '-';

        if ($current) {
            $returnData->currentVersion = substr_replace($current->v,'.', 1, 0);
            $returnData->currentDate = $current->date;
        }

        $thisVersion = $this->getThisVersion();
        $returnData->thisVersion = 'Błąd połączenia';

        if ($thisVersion) {
            $returnData->thisVersion = substr_replace($thisVersion,'.', 1, 0);
        }

        $returnData->isUp2date = ($returnData->currentVersion <= $returnData->thisVersion);
        $returnData->htmlIconStatus = $returnData->isUp2date ? '<i class="pe-7s-check" style="color:#49a94d;"></i>' : '<i class="pe-7s-attention" style="color:#a94442;"></i>';

        $returnData->colorHex = $returnData->isUp2date ? '#49a94d;' : '#a94442';
        $returnData->balanceRow = $this->getBalanceHtml();

        return $returnData;
    }

    public function generateOutput($data)
    {
        return <<<EOF
<div class="widget-content-padded icon-stats">
    <div class="row">
        <div class="col-sm-7">
            <div class="item">
                <div class="icon-holder text-center">
                    $data->htmlIconStatus
                </div>
                <div class="data">
                    <div class="note">
                        Aktualna używana wersja
                    </div>
                    <div class="number">
                        <span style="color:$data->colorHex;">$data->thisVersion</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-5 text-right">
            <a href="https://domeny.tv/files/whmcs.zip" class="btn btn-default btn-sm">
                <i class="fas fa-download"></i> Pobierz aktualny moduł
            </a>
        </div>
    </div>
    <div class="row">
    <div class="col-sm-7">
        <div class="item">
            <div class="icon-holder text-center">
            </div>
            <div class="data">
                <div class="note">
                    Najnowsza dostępna wersja
                </div>
                <div class="number">
                    <span style="color:$data->colorHex;">$data->currentVersion</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-5 text-right">
        <div class="data">
                <div class="note">
                    Data publikacji
                </div>
                <div class="number">
                    <span style="color:$data->colorHex;">$data->currentDate</span>
                </div>
            </div>
    </div>
    </div>
    $data->balanceRow
</div>
EOF;
    }
}

add_hook("AdminHomeWidgets", 1, function () {
    return new DomenyTvStatusWidget;
});
