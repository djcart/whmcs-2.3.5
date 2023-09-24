<?php
/**
 * Domeny.tv - Plugin module for WHMCS
 *
 * @package   Domeny.tv registrars plugin - hooks
 * @author    MSERWIS
 * @copyright 2022 MSERWIS
 * @version   V2.35
 * @link      http://www.domeny.tv/reseller
 */

function SQL2array($sql)
{
   $query = mysql_query($sql);
   $data = array();
   while ($qa = mysql_fetch_assoc($query))
   {
      $data[] = $qa;
   }

   return $data;
}


function sendApiCmd($action, $data2send, $params = array())
{
   $domenytv_adress = array(
     'production' => 'https://www.domeny.tv/regapi/soap.wsdl.xml',
     'test' => 'https://www.domeny.tv/regapi/test/soap.wsdl.xml'
   );

   if($params['TestMode'] == 'on')
   {
      $adress = $domenytv_adress['test'];
   }
   else
   {
      $adress = $domenytv_adress['production'];
   }

   $soapClient = new SoapClient($adress);

   $data2send_log = $data2send;

   try
   {
      if(isset($params["Password"]))
      {
         $data2send['password'] = $params["Password"];
      }

      if(isset($params["Username"]))
      {
         $data2send['login'] = $params["Username"];
      }

      $soap_response = $soapClient->$action($data2send);

      $response = array('r_status' => 1);
      $response = array_merge($response, $soap_response);

      if($soap_response['result'] != 1000)
      {
         $response['r_status'] = 0;
         $response['error_no'] = $soap_response['result'];
         $response['error_msg'] = (isset($m_errors[$soap_response['result']])? $m_errors[$soap_response['result']] : 'Unknown');
         $response['error'] = $soap_response['result'].' - '.$response['error_msg'];

      }
   }
   catch(SoapFault $fault)
   {
      $response['error'] = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
   }

   $replacevars = array();
   logModuleCall('domenytv',$action,$data2send_log,$response,$response,$replacevars);
   return $response;
}



function getDoemnyTvSettings()
{
   $retunValues = array();

   $sql = "SELECT * FROM tblregistrars WHERE registrar = 'domenytv' ";
   $data = SQL2array($sql);

   foreach($data as &$param)
   {
      $command = "decryptpassword";
      $values["password2"] = $param['value'];
      $decoded = localAPI($command, $values);

      if(is_array($decoded) && isset($decoded['result']) && $decoded['result'] == 'success')
      {
        $retunValues[$param['setting']] = $decoded['password'];
      }
   }

   return $retunValues;
}

function getDomainNameServers($domainId)
{
   $sql = "
      SELECT nameservers
      FROM tbldomains d
      LEFT JOIN tblorders o ON o.id = d.orderid
      WHERE d.id = '".intval($domainId)."'
   ";

   $queryData = SQL2array($sql);


   if(is_array($queryData) && count($queryData) > 0)
   {
      $nameServers = array();
      $nameServersDb = explode(',', $queryData[0]['nameservers']);

            $serverNo = 1;
      foreach($nameServersDb as $nameServer)
      {
         $nameServers['NameServer'.$serverNo] = $nameServer;
         $serverNo++;
      }

      return $nameServers;
   }

   return false;
}

function getDomainData($domainId)
{
   $sql = "
      SELECT *
      FROM tbldomains
      WHERE id = '".intval($domainId)."'
      LIMIT 1
   ";

   $queryData = SQL2array($sql);

   if(is_array($queryData) && count($queryData) > 0)
   {
      return  $queryData[0];
   }

   return false;
}


function bookDomain($domain, $domainData)
{
   $domainCheck = explode('.', $domain);

   if(end($domainCheck) !== 'pl')
   {
      return false;
   }

   $settings = getDoemnyTvSettings();

   $dns = getDomainNameServers($domainData['id']);

   $data2send = array(
      'domain' => $domain,
      'dns' => $dns,
   );

   $response = sendApiCmd('bookDomain', $data2send, $settings);

   return $response;
}


function bookDomainHOOK($params)
{
   if(is_array($params) && isset($params['DomainIDs']) && count($params['DomainIDs']) > 0)
   {
      foreach($params['DomainIDs'] as $domainId)
      {
         $domainData = getDomainData($domainId);

         if($domainData)
         {
            bookDomain($domainData['domain'], $domainData);
         }
      }
   }
}
