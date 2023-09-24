<?php
namespace WHMCS\Module\Addon\domenytv\Admin\Controllers\Prices;

use WHMCS\Database\Capsule;
use WHMCS\Module\Common\domenytv\Admin\Controllers\ApiController;
use WHMCS\Module\Common\domenytv\Admin\AdminDispatcher;

class PricesUpdater
{
    private $pricesTableDomainPrefix = 'domain';
    private $currencyId = 1;
    private $periods2update = array(
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
    );
    private $dtvPrices = null;
    public $types = array(
        'register', 'renew', 'transfer',
    );
    private $castPricesToYears = array(
        'msetupfee' => 1,
        'qsetupfee' => 2,
        'ssetupfee' => 3,
        'asetupfee' => 4,
        'bsetupfee' => 5,
        'monthly' => 6,
        'quarterly' => 7,
        'semiannually' => 8,
        'annually' => 8,
        'biennially' => 10,
    );

    public function __construct()
    {
        $login = AdminDispatcher::$params['api_login'];
        $password = AdminDispatcher::$params['api_password'];
        $this->api = new ApiController('https://www.domeny.tv/regapi/soap.wsdl.xml', $login, $password);
    }

    public function setCurrencyId($id) {
        $this->currencyId = $id;
    }

    public function updateExtension($domainPrices)
    {
        $extension = '.' . $domainPrices->ext;

        $domainData = Capsule::table('tbldomainpricing')->where('extension', $extension)->first();

        if (!$domainData) {

            $extId = $this->addNewExtension($extension);

        } else {
            $extId = $domainData->id;
        }

        $this->updatePrices4extensionId($extId, $domainPrices);
    }

    private function updatePrices4extensionId($extId, $tldData)
    {
        foreach ($this->types as $type) {
            if ($tldData->custom->{$type} == null) {
                continue;
            }

            $typeString = $this->pricesTableDomainPrefix . $type;
            $priceTypeData = Capsule::table('tblpricing')
                ->where('type', 'LIKE', $typeString)
                ->where('currency', (int) $this->currencyId)
                ->where('relid', (int) $extId)
                ->first();

            if (!$priceTypeData) {

                Capsule::table('tblpricing')
                    ->insert(
                        array(
                            'type' => $typeString,
                            'relid' => $extId,
                            'currency' => $this->currencyId,
                            'tsetupfee' => 0.00,
                            'msetupfee' => $tldData->custom->{$type}->{1},
                            'qsetupfee' => $tldData->custom->{$type}->{2},
                            'ssetupfee' => $tldData->custom->{$type}->{3},
                            'asetupfee' => $tldData->custom->{$type}->{4},
                            'bsetupfee' => $tldData->custom->{$type}->{5},
                            'monthly' => $tldData->custom->{$type}->{6},
                            'quarterly' => $tldData->custom->{$type}->{7},
                            'semiannually' => $tldData->custom->{$type}->{8},
                            'annually' => $tldData->custom->{$type}->{9},
                            'biennially' => $tldData->custom->{$type}->{10},
                        )
                    );
            } else {
                Capsule::table('tblpricing')
                    ->where('type', 'LIKE', $typeString)
                    ->where('currency', (int) $this->currencyId)
                    ->where('relid', (int) $extId)
                    ->update(
                        array(
                            'msetupfee' => $tldData->custom->{$type}->{1},
                            'qsetupfee' => $tldData->custom->{$type}->{2},
                            'ssetupfee' => $tldData->custom->{$type}->{3},
                            'asetupfee' => $tldData->custom->{$type}->{4},
                            'bsetupfee' => $tldData->custom->{$type}->{5},
                            'monthly' => $tldData->custom->{$type}->{6},
                            'quarterly' => $tldData->custom->{$type}->{7},
                            'semiannually' => $tldData->custom->{$type}->{8},
                            'annually' => $tldData->custom->{$type}->{9},
                            'biennially' => $tldData->custom->{$type}->{10},
                        )
                    );
            }
        }
    }

    private function addNewExtension($extension)
    {

        Capsule::table('tbldomainpricing')
            ->insert(
                array(
                    'extension' => $extension,
                )
            );

        return Capsule::connection()->getPdo()->lastInsertId();
    }

    private function getDomainsPricesInWhmcs()
    {
        $domains = Capsule::table('tbldomainpricing')->get();

        $domainsPricesArray = array();
        foreach ($domains as $domainData) {
            $domainsPricesArray[$domainData->extension] = self::getWhmcsPriceForTld($domainData->id);
        }

        return $domainsPricesArray;
    }

    public function getWhmcsPriceForTld($extId)
    {
        $prices = Capsule::table('tblpricing')
            ->where('type', 'LIKE', $this->pricesTableDomainPrefix . '%')
            ->where('currency', (int) $this->currencyId)
            ->where('relid', (int) $extId)
            ->get();

        $pricesArray = array();

        foreach ($prices as $price) {
            $type = str_replace($this->pricesTableDomainPrefix, '', $price->type);
            $pricesArray[$type] = array(
                1 => (float) $price->msetupfee,
                2 => (float) $price->qsetupfee,
                3 => (float) $price->ssetupfee,
                4 => (float) $price->asetupfee,
                5 => (float) $price->bsetupfee,
                6 => (float) $price->monthly,
                7 => (float) $price->quarterly,
                8 => (float) $price->semiannually,
                9 => (float) $price->annually,
                10 => (float) $price->biennially,
            );
        }

        foreach ($this->types as $type) {
            if (!isset($pricesArray[$type])) {
                $pricesArray[$type] = array(
                    1 => '-',
                    2 => '-',
                    3 => '-',
                    4 => '-',
                    5 => '-',
                    6 => '-',
                    7 => '-',
                    8 => '-',
                    9 => '-',
                    10 => '-',
                );
            }
        }

        return $pricesArray;
    }

    public function getPricesMatrix()
    {

        $dtvPrices = $this->api->getDomainsPrices();

        foreach ($dtvPrices['domains'] as $index => $domainPrices) {

            $extension = '.' . $domainPrices['ext'];

            $domainData = Capsule::table('tbldomainpricing')->where('extension', $extension)->first();

            if (!$domainData) {
                $dtvPrices['domains'][$index]['inWhmcs'] = false;

            } else {
                $dtvPrices['domains'][$index]['inWhmcs'] = true;
            }

            $dtvPrices['domains'][$index]['whmcs'] = $this->getWhmcsPriceForTld($domainData->id);

            $dtvPrices['domains'][$index]['idprotect'] = $dtvPrices['domains'][$index]['idprotect'] == 'true' ? true : false;
            $dtvPrices['domains'][$index]['trd_price'] = $dtvPrices['domains'][$index]['trd_price'] == 'undefined' ? 0 : $dtvPrices['domains'][$index]['trd_price'];

            foreach ($this->types as $type) {
                $typeString = $this->pricesTableDomainPrefix . $type;
                $price1 = -1.00;

                switch ($type) {
                    case 'register':
                        $price1 = $domainPrices['reg_price'];
                        break;
                    case 'renew':
                        $price1 = $domainPrices['ren_price'];
                        break;
                    case 'transfer':
                        $price1 = $domainPrices['tra_price'];
                        break;
                }

                if ($this->commissionPer > 0) {
                    $price1 *= $this->commissionPer / 100;
                }

                if ($this->commissionValue > 0) {
                    $price1 += $this->commissionValue;
                }

                $dtvPrices['domains'][$index][$type] = array(
                    1 => $price1,
                    2 => $price1 + (1 * $domainPrices['ren_price']),
                    3 => $price1 + (2 * $domainPrices['ren_price']),
                    4 => $price1 + (3 * $domainPrices['ren_price']),
                    5 => $price1 + (4 * $domainPrices['ren_price']),
                    6 => $price1 + (5 * $domainPrices['ren_price']),
                    7 => $price1 + (6 * $domainPrices['ren_price']),
                    8 => $price1 + (7 * $domainPrices['ren_price']),
                    9 => $price1 + (8 * $domainPrices['ren_price']),
                    10 => $price1 + (9 * $domainPrices['ren_price']),
                );
            }
        }

        return $dtvPrices['domains'];
    }

    public function getCurrencies() {
        $currencies = Capsule::table('tblcurrencies')
        ->get();

        $currencyData = [];
        foreach($currencies as $currency) {
            $currencyData[] = [
                'id' => $currency->id,
                'code' => $currency->code,
                'prefix' => $currency->prefix,
                'suffix' => $currency->suffix,
                'format' => $currency->format,
                'rate' => $currency->rate,
                'default' => $currency->default
            ];
        }

        return $currencyData;
    }

}
