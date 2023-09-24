<?php

namespace WHMCS\Module\Addon\domenytv\Admin\Controllers\Ssl;

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\domenytv\Admin\Controllers\AjxController;
use WHMCS\Module\Common\domenytv\Lang;

/**
 * Undocumented class
 */
class SslApiController extends AjxController
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function generateCsr()
    {
        $inputData = $this->getJsonPost();
        $countryName = strtoupper($inputData->country);
        $state = $inputData->state;
        $city = $inputData->city;
        $organization = $inputData->organization;
        $unitName = $inputData->unit;
        $name = substr($inputData->domain, 0, 63);
        $email = $inputData->email;
        $key = $inputData->key == '1' ? 4096 : 2048;

        $errors = array();

        if (preg_match('%[ąćęłńóśżź]%si', $this->getRawJsonPost())) {
            $errors[] = 'Niedozowlone znaki - nie używaj polskich znaków. ';
        }

        if (!preg_match('/^(\*\.)?[a-z0-9-]+[a-z0-9.-]*\.[a-z]{2,30}$/i', $name)) {
            $errors[] = Lang::t('n_csr_wrong_domain_name');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = Lang::t('n_csr_wrong_email');
        }
        if (strlen($organization) < 2 || strlen($unitName) < 2 || strlen($city) < 2 || strlen($state) < 2 || strlen($countryName) < 2) {
            $errors[] = Lang::t('n_csr_invalid_data');
        }

        if (count($errors)) {
            $data = array('errors' => $errors);
            $this->response->setMessages($errors, 'error');
            $this->response->respond();
        } else {
            $data = $this->api->generateCSR($countryName, $state, $city, $organization, $unitName, $name, $email, $key);
            $this->response->respond($data);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function verifySslOrder()
    {
        $inputData = $this->getJsonPost();
        $orderData = array(
            'product' => $inputData->product,
            'period' => $inputData->period,
            'csr' => $inputData->csr,
            'first_name' => $inputData->first_name,
            'last_name' => $inputData->last_name,
            'phone' => '+'.trim(str_replace(['+'], '', $inputData->phonenumber)),
            'email' => $inputData->email,
            'admin_email' => $inputData->admin_mail,
            'company' => $inputData->company,
            'address' => $inputData->address,
            'city' => $inputData->city,
            'district' => $inputData->district,
            'zip' => $inputData->zip,
            'nip' => $inputData->nip,
            'country' => $inputData->country,
            'language' => "PL",
        );

        $data = [];

        $verifyResponse = $this->api->sslVerifyOrder($orderData);
        if (isset($verifyResponse['result']) && $verifyResponse['result'] == 1000) {
            $orderResponse = $this->orderSsl($orderData);

            if (isset($orderResponse['result']) && $orderResponse['result'] == 1000) {
                $this->response->setMessages(['Certyfikat został zamówiony'], 'success');
                $data = ['ssl' => true];
            } else {
                $this->response->setMessages([$orderResponse['result'].' '.$orderResponse['result_msg']], 'error');
            }

        } else {
            $this->response->setMessages([$verifyResponse['result'].' '.$verifyResponse['result_msg']], 'error');
        }

        $this->response->respond($data);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function checkCsr()
    {
        $inputData = $this->getJsonPost();
        $csr = trim($inputData->csr);
        $csrArray = openssl_csr_get_subject($csr, 1);

        $returnArray = array();

        if (isset($csrArray['CN'])) {
            $returnArray['domain'] = $csrArray['CN'];
        }

        $this->response->respond($returnArray);
    }

    private function orderSsl($data)
    {
        $orderResponse = $this->api->sslPlaceOrder($data);

        if ($orderResponse['result'] == 1000) {



            $inputData = $this->getJsonPost();
            $csr = trim($inputData->csr);
            $csrArray = openssl_csr_get_subject($csr, 1);

            $orderId = $orderResponse['order_id'];

            $data = array_map(function($entry) {
                if ($entry === null) {
                    $entry = '';
                }

                return $entry;
            }, $data);

            Capsule::connection()->transaction(
                function ($connectionManager) use ($data, $csrArray, $orderId) {
                    $connectionManager
                        ->table('dtv_ssl_orders')
                        ->insert(
                            array(
                                'product' => $data['product'] | '' ,
                                'period' => $data['period'] | '',
                                'first_name' => $data['first_name'] | '',
                                'last_name' => $data['last_name'] | '',
                                'phone' => $data['phone'] | '',
                                'email' => $data['email'] | '',
                                'admin_email' => $data['admin_email'] | '',
                                'company' => $data['company'] | '',
                                'address' => $data['address'] | '',
                                'city' => $data['city'] | '',
                                'district' => $data['district'] | '',
                                'zip' => $data['zip'] | '',
                                'nip' => $data['nip'] | '',
                                'country' => $data['country'] | '',
                                'status' => 0,
                                'domain' => $csrArray['CN'] | '',
                                'add_date' => date('Y-m-d H:i:s'),
                                'order_id' => $orderId | '',
                            )
                        );
                }

            );
        }

        return $orderResponse;
    }

    public function getOrders() {
        $orders = Capsule::table('dtv_ssl_orders')
            ->orderBy('add_date', 'desc')
            // ->offset($offset)
            // ->limit($limit)
            ->get();

        $this->response->respond($orders->toArray());
    }

    public function getOrderStatus() {
        $inputData = $this->getJsonPost();
        $status = $this->getSslOrderStatus((int)$inputData->id);

        if ($status == false){
            $this->response->addMessage('Nie udało się pobrać informacje o statusie tego zamówienia...', 'error');
        } else {
            $updateDate = date('Y-m-d H:i:s');

            Capsule::table('dtv_ssl_orders')
                ->where('order_id', (int)$inputData->id)
                ->update(
                    array(
                        'status' => $status,
                        'update_date' => $updateDate,
                    )
                );
        }

        $this->response->respond(['status' => $status, 'update_date' => $updateDate]);
    }

    private function updateSslOrderStatus($orderId)
    {
        $status = $this->getSslOrderStatus($orderId);

        if ($status !== false) {
            Capsule::table('dtv_ssl_orders')
                ->where('id', $orderId)
                ->update(
                    array(
                        'status' => $status,
                    )
                );

            return $status;
        }

        return null;
    }

    private function getSslOrderStatus($orderId)
    {

         $data = [ 'order_id' => $orderId ];

        $response = $this->api->sslCheckOrder($data);


        if ($response['result'] == 1000) {

            switch ($response['status1']) {
                case 'PENDING':
                    $status = 0;
                    break;
                case 'CANCELLED':
                    $status = 2;
                    break;
                case 'COMPLETE':
                    $status = 1;
                    break;
                case 'NO_INFO':
                    $status = 3;
                    break;
                default:
                    $status = 666;
            }

            return $status;
        }

        return false;
    }
}
