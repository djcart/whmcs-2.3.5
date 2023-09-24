<?php

namespace WHMCS\Module\Common\domenytv;

use ArrayObject;
use SoapFault;
use WHMCS\Module\Common\domenytv\Admin\AdminDispatcher;

class ApiResponse extends ArrayObject
{

    /**
     * __construct
     *
     * @param  array $response
     *
     * @return void
     */
    public function __construct($response)
    {
        if (is_object($response) && get_class($response) == SoapFault::class) {
            $this['error'] = "SOAP Fault: (faultcode: {$response->faultcode}, faultstring: {$response->faultstring})";
            $this['r_status'] = 0;
            return $this;
        }

        parent::__construct($response);

        $this['r_status'] = 1;
        $this['result_msg'] = null;

        if (!isset($response['result'])) {
            return;
        }

        $response_msg = Errors::getByCode($response['result'], AdminDispatcher::$lang);

        /* Check if response returned error code */
        if ($response['result'] != 1000 && $response['result'] != 1001) {
            $this['r_status'] = 0;
            $this['error_no'] = $response['result'];
            $this['error_msg'] = $response_msg;
            $this['error'] = $response['result'] . ' - ' . $response_msg;
        }

        if ($response['result'] != 1000) {
            /* Set response message (if not error) */
            $this['result_msg'] = $response_msg;
        }

        return;
    }

    /**
     * Check if response is ok
     *
     * @return bool
     */
    public function ok()
    {
        return !$this->error();
    }

    /**
     * Check if response returned error
     *
     * @return bool
     */
    public function error()
    {
        return $this['r_status'] === 0;
    }

    /**
     * Convert array object to array
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
    }

}
