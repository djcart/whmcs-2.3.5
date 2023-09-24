<?php

namespace WHMCS\Module\Registrar\domenytv\Admin\Controllers\Registrar;

use WHMCS\Module\Common\domenytv\Admin\Controllers\Controller;
use WHMCS\Module\Common\domenytv\Errors;
use WHMCS\Module\Common\helpers\PunyCode;
use WHMCS\Module\Common\helpers\Utils;

class RegistrarController extends Controller
{

    /**
     * Check domain availability
     *
     * @param  array $params
     *
     * @return array
     */
    public function checkAvailability($params)
    {
        $searchTerm = $params['searchTerm'];
        $punyCodeSearchTerm = $params['punyCodeSearchTerm'];
        $tldsToInclude = $params['tldsToInclude'];

        $results = new \WHMCS\Domains\DomainLookup\ResultsList();

        foreach ($tldsToInclude as $tld) {

            $tld = preg_replace('%^\.%i', '', $tld);

            $data2send = array(
                'domain' => $searchTerm . '.' . $tld,
            );

            $response = $this->api->checkDomainExtended($data2send);

            $searchResult = new \WHMCS\Domains\DomainLookup\SearchResult($searchTerm, $tld);

            $avCodes = array(1000, 9072);

            if ($response['r_status'] == 1 && in_array($response['result'], $avCodes)) {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED;

                if ($response['premium'] == 'true') {
                    $searchResult->setPremiumDomain(true);
                    $searchResult->setPremiumCostPricing(
                        array(
                            'register' => $response['price'],
                            'renew' => $response['price'],
                            'CurrencyCode' => 'PLN',
                        )
                    );
                }
            } else {
                $status = \WHMCS\Domains\DomainLookup\SearchResult::STATUS_REGISTERED;
            }

            $searchResult->setStatus($status);
            $results->append($searchResult);
        }

        return $results;
    }

    /**
     * Delete nameserver
     *
     * @param  array $params
     *
     * @return array
     */
    public function deleteNameserver($params)
    {
        $values["error"] = 'You can not delete host - contact your supplier';
        return $values;
    }

    /**
     * Get contact details
     *
     * @param  array $params
     *
     * @return array
     */
    public function getContactDetails($params)
    {
        $sld = $params['sld'];
        $tld = $params['tld'];

        $data2send = array('domain' => $sld . '.' . $tld);

        $response = $this->api->contactInfo($data2send);

        return array(
            'Registrant' => array(
                'Name [not editable]' => $response['name'],
                'Organization [not editable]' => $response['org'],
                'Email Address' => $response['email'],
                'Address' => $response['address'],
                'City' => $response['city'],
                'State' => $response['district'],
                'Postcode' => $response['zip'],
                'Country' => $response['country'],
                'Phone Number' => $response['phone'],
                'Fax Number' => $response['fax'],
            ),
        );
    }

    /**
     * Get DNS
     *
     * @param  array $params
     *
     * @return ApiResponse | array
     */
    public function getDNS($params)
    {
        $domain = isset($params['original']['domainname']) ? $params['original']['domainname'] : $params['domainname'];
        $domain = strtolower($domain);

        $response = $this->api->checkRedirectionServer(array('domain' => $domain));

        if ($response['result'] == 64) {
            $response = $this->api->activateRedirectionServer(array('domain' => $domain));


            if ($response != '1000') {
                return $response;
            }
        }

        if ($response != '1000') {
            return array(
                'error' => $response['error'],
            );
        }


        $data2send = array(
            'domain' => $domain,
        );

        $response = $this->api->getDomainDNSRecords($data2send);

        if ($response->ok()) {
            $hostrecords = array();
            foreach ($response['records'] as $record) {
                $hostrecords[] = array("hostname" => $record['subdomain'], "type" => $record['type'], "address" => $record['value'], "priority" => $record['priority']);
            }

            return $hostrecords;
        }

        return $response;
    }

    /**
     * Get domain suggestions
     *
     * @return ResultList
     */
    public function getDomainSuggestions()
    {
        $results = new \WHMCS\Domains\DomainLookup\ResultsList;
        return $results;
    }

    /**
     * Get EPP Code
     *
     * @param  array $params
     *
     * @return mixed
     */
    public function getEPPCode($params)
    {

        if ($this->isClientArea() && $params['DisableTransferOut'] === 'on') {
            $values['error'] = Errors::getByCode('transfer_out_disabled', $this->lang);
            return $values;
        }

        $domain = strtolower($params['sld'] . '.' . $params['tld']);
        $data2send = array(
            'domain' => $domain,
        );

        $response = $this->api->domainGetAuthinfo($data2send);

        if ($response->ok()) {
            $values['eppcode'] = $response['authinfo'];
            return $values;
        }

        return $response;
    }

    /**
     * Get nameservers
     *
     * @param array $params
     *
     * @return ApiResponse | array
     */
    public function getNameservers($params)
    {

        $domain = isset($params['original']['domainname']) ? $params['original']['domainname'] : $params['domainname'];

        $data2send = array(
            'domain' => $domain,
        );

        $response = $this->api->domainInfo($data2send);
        $values = [];

        if ($response && $response['result'] == 1000) {
            $ns_no = 1;
            foreach ($response['ns'] as $ns) {
                $values['ns' . $ns_no] = $ns;
                $ns_no++;
            }

            return $values;
        }

        return $response;
    }

    /**
     * Get registrar lock status
     *
     * @param array $params
     *
     * @return bool
     */
    public function getRegistrarLock($params)
    {
        $data2send = array(
            'domain' => $params['original']['domainname'],
        );

        $response = $this->api->registrarLockCheck($data2send);

        if ($response['r_status'] == 0) {
            return false;
        } else {
            if (isset($response['hasLock'])) {
                if ($response['hasLock'] == 'true') {
                    return 'locked';
                } else {
                    if (isset($response['isLockable']) && $response['isLockable']) {
                        return 'unlocked';
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Get users domains
     *
     * @param array $params
     * @param boolean $force Force cache refresh
     *
     * @return ApiResponse
     */
    private function getUsersDomains($forceCache = false)
    {
        return $this->api->getAllDomains($forceCache);
    }

    /**
     * Toggle ID Protect
     *
     * @param array $params
     *
     * @return ApiResponse
     */
    public function IDProtectToggle($params)
    {
        $data2send = array(
            'domain' => $params['domainname'],
        );

        $responseCheck = $this->api->idProtectCheckStatus($data2send);

        if (isset($responseCheck['error']) && $responseCheck['result'] != '95') {
            return $responseCheck;
        }

        if (isset($params['protectenable'])) {
            if ($params['protectenable']) {
                if ($responseCheck['result'] == '95') { //buy
                    $operation = 'idProtectPurchase';
                } else {
                    $operation = 'idProtectEnable';
                }
            } else {
                $operation = 'idProtectDisable';
            }

            return $this->api->$operation($data2send);
        }
    }

    /**
     * Modify nameserver
     *
     * @param array $params
     *
     * @return ApiResponse
     */
    public function modifyNameserver($params)
    {
        $data2send = array(
            'host' => $params["nameserver"],
            'ip' => $params["newipaddress"],
        );

        $response = $this->hostUpdate($data2send);

        return $response;
    }

    /**
     * Register domain
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function registerDomain($params)
    {

        if (trim($params['phonecc']) == '') {
            $phone_no = $params['phonenumber'];
            $val['error'] = 'Invalid phone number "' . $phone_no . '", Please check phone number entered in the client\'s profile: "' . $params['phonenumber'] . '", it should be country prefix and 7 to 11 digits phone number separated by a dot, i.e. +48.123123123.';
        } else {
            $phone_no = '+' . $params['phonecc'] . '.' . $params['phonenumber'];
            $val['error'] = 'Invalid phone number "' . $phone_no . '", country prefix for selected contact is  "+' . $params['phonecc'] . '" (' . $params['countrycode'] . ') - Please check phone number entered in the client\'s profile: "' . $params['phonenumber'] . '", it should be 7 to 11 digits without country prefix.';
        }

        if (!preg_match('%^\+[0-9]{1,3}\.[0-9]{7,11}$%', $phone_no)) {
            return $val;
        }

        $domain = $params['domainname'];

        $dns = array(
            'NameServer1' => $params['original']['ns1'] ? trim($params['original']['ns1']) : trim($params['ns1']),
            'NameServer2' => $params['original']['ns2'] ? trim($params['original']['ns2']) : trim($params['ns2']),
            'NameServer3' => $params['original']['ns3'] ? trim($params['original']['ns3']) : trim($params['ns3']),
            'NameServer4' => $params['original']['ns4'] ? trim($params['original']['ns4']) : trim($params['ns4']),
            'NameServer5' => $params['original']['ns5'] ? trim($params['original']['ns5']) : trim($params['ns5']),
        );


        //custom fields

        $idNumber = '';

        if (isset($params['tax_id']) &&  $params['tax_id'] != '') {
            $idNumber = $params['tax_id'];
        }

        $registrant = array(
            'companyName' => $params['companyname'],
            'firstName' => $params['firstname'],
            'lastName' => $params['lastname'],
            'address' => $params['address1'] . ((trim($params['address2']) != '') ? ' ' . trim($params['address2']) : ''),
            'zip' => $params['postcode'],
            'district' => $params['state'],
            'city' => $params['city'],
            'country' => (trim($params['countrycode']) != '') ? $params['countrycode'] : strtoupper($params["country"]),
            'phone' => $phone_no,
            'fax' => '',
            'email' => $params['email'],
            'idNumber' => $idNumber,
            'passNumber' => Utils::getCustomField($params, ['Numer dokumentu', 'Document number']),
            'dob' =>  Utils::getCustomField($params, ['Data urodzenia', 'Date of birth']),
            'placeOfBirth' => Utils::getCustomField($params, ['Kraj urodzenia', 'Country of birth'])

        );

        $data2send = array(
            'domain' => $domain,
            'registrant' => $registrant,
            'period' => intval($params['regperiod']),
            'dns' => $dns,
        );

        $response = $this->api->registerDomain($data2send);

        return $response;
    }

    /**
     * Register nameserver
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function registerNameserver($params)
    {
        $data2send = array(
            'host' => $params["nameserver"],
            'ip' => $params["ipaddress"],
        );

        $response = $this->api->hostCreate($data2send);

        return $response;
    }

    /**
     * Renew domain
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function renewDomain($params)
    {
        $domain = isset($params['original']['domainname']) ? $params['original']['domainname'] : $params['domainname'];

        $data2send = array(
            'domain' => $domain,
            'period' => intval($params['regperiod']),
        );

        $response = $this->api->domainRenew($data2send);

        return $response;
    }

    /**
     * Save contact details
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function saveContactDetails($params)
    {
        $contactDetails = $params['contactdetails']['Registrant'];

        $sld = $params['sld'];
        $tld = $params['tld'];

        $registrant = array(
            'address' => $contactDetails['Address'],
            'zip' => $contactDetails['Postcode'],
            'district' => $contactDetails['State'],
            'city' => $contactDetails['City'],
            'country' => $contactDetails['Country'],
            'phone' => $contactDetails['Phone Number'],
            'fax' => $contactDetails['Fax Number'],
            'email' => $contactDetails['Email Address'],
        );

        $data2send = array(
            'domain' => $sld . '.' . $tld,
            'registrant' => $registrant,
        );

        return $response = $this->api->contactUpdate($data2send);
    }

    /**
     * Save DMS
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function saveDNS($params)
    {
        $domain = strtolower($params['sld'] . '.' . $params['tld']);
        $DNSrecords = array();

        foreach ($params['dnsrecords'] as $record) {
            if (empty($record['hostname']) || empty($record['type']) || empty($record['address'])) {
                continue;
            }

            $type = $record['type'];

            if ($type == 'URL') {
                $type = 'REDIRECT';
            }

            $DNSrecords[] = array(
                'subdomain' => $record['hostname'],
                'type' => $type,
                'priority' => $record['priority'],
                'value' => $record['address'],
            );
        }

        $data2send = array(
            'domain' => $domain,
            'records' => $DNSrecords,
        );

        return $this->api->setDomainDNSRecords($data2send);
    }

    /**
     * Save nameservers
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function saveNameservers($params)
    {
        $domain = isset($params['original']['domainname']) ? $params['original']['domainname'] : $params['domainname'];

        $dns = array(
            'NameServer1' => $params['original']['ns1'] ? trim($params['original']['ns1']) : trim($params['ns1']),
            'NameServer2' => $params['original']['ns2'] ? trim($params['original']['ns2']) : trim($params['ns2']),
            'NameServer3' => $params['original']['ns3'] ? trim($params['original']['ns3']) : trim($params['ns3']),
            'NameServer4' => $params['original']['ns4'] ? trim($params['original']['ns4']) : trim($params['ns4']),
            'NameServer5' => $params['original']['ns5'] ? trim($params['original']['ns5']) : trim($params['ns5']),
        );

        $data2send = array(
            'domain' => $domain,
            'dns' => $dns,
        );

        return $this->api->changeDomainNameservers($data2send);
    }

    /**
     * Save registrar lock
     *
     * @param  array $params
     *
     * @return mixed
     */
    public function saveRegistrarLock($params)
    {
        if (isset($params['lockenabled'])) {

            if ($params['lockenabled'] == 'locked') {
                $operation = 'registrarLockSet';
            } else {
                $operation = 'registrarLockRemove';
            }

            $data2send = array(
                'domain' => $params['domainname'],
            );

            return $this->api->$operation($data2send);
        }
    }

    /**
     * Sync
     *
     * @param  array $params
     *
     * @return array
     */
    public function sync($params)
    {
        $response = $this->getUsersDomains();

        $domainStatus = false;

        foreach ($response['domains'] as $domainData) {
            $domain = $domainData['domain_name'] . '.' . $domainData['domain_ext'];

            $domain = PunyCode::decode($domain);
            if (strtolower($domain) == strtolower($params['domain'])) {
                $domainStatus = $domainData;
            }
        }

        if (isset($response['result']) && $response['result'] == 1000) {
            if ($domainStatus) {
                $active = (strtotime(substr($domainStatus['exp_date'], 0, 10) . ' 00:00:00') < time());
                $values['expirydate'] = substr($domainStatus['renew_date'], 0, 10);
                $values['active'] = $active;
                $values['expired'] = !$active;
                $values['transferredAway'] = false;
            } else {
                $values['error'] = 'not found';
            }
        } else {
            $values['error'] = $response['error_msg'];
        }

        return $values;
    }

    /**
     * Transfer Domain
     *
     * @param  array $params
     *
     * @return ApiResponse
     */
    public function transferDomain($params)
    {
        $domain = $params['domainname'];

        $registrant = array(
            'companyName' => $params['companyname'],
            'firstName' => $params['firstname'],
            'lastName' => $params['lastname'],
            'address' => $params['address1'] . ((trim($params['address2']) != '') ? ' ' . trim($params['address2']) : ''),
            'zip' => $params['postcode'],
            'district' => $params['state'],
            'city' => $params['city'],
            'country' => $params['countrycode'],
            'phone' => '+' . $params['phonecc'] . '.' . $params['phonenumber'],
            'fax' => '',
            'email' => $params['email'],
            'idNumber' => isset($params['tax_id']) ? $params['tax_id'] : '',
            'passNumber' => Utils::getCustomField($params, ['Numer dokumentu', 'Document number']),
            'dob' =>  Utils::getCustomField($params, ['Data urodzenia', 'Date of birth']),
            'placeOfBirth' => Utils::getCustomField($params, ['Kraj urodzenia', 'Country of birth'])
        );

        $eppCode = (isset($params["eppcode"]) && $params["eppcode"] != '') ? $params["eppcode"] : $params['transfersecret'];
        $data2send = array(
            'domain' => $domain,
            'contact' => $registrant,
            'authinfo' => $eppCode,
        );

        $response = $this->api->transferDomain($data2send);


        if (isset($response['result']) && ($response['result'] == 1000 || $response['result'] == 1001)) {
            return array(
                'success' => true,
            );

        } else {
            return array(
                'error' => $response['error_msg'],
            );
        }

    }

    /**
     * TransferSync
     *
     * @param  array $params
     *
     * @return array
     */
    public function transferSync($params)
    {
        $response = $this->getUsersDomains();

        $domainStatus = false;

        foreach ($response['domains'] as $domainData) {
            $domain = $domainData['domain_name'] . '.' . $domainData['domain_ext'];
            if (strtolower($domain) == strtolower($params['domain'])) {
                $domainStatus = $domainData;
            }
        }

        if (isset($response['result']) && $response['result'] == 1000) {
            if ($domainStatus) {
                $values['completed'] = true;
                $values['expirydate'] = substr($domainStatus['exp_date'], 0, 10);
            } else {
                $values['error'] = 'not found';
            }
        } else {
            $values['error'] = $response['error_msg'];
        }

        return $values;
    }
}
