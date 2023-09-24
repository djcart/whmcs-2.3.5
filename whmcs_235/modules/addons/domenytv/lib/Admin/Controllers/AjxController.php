<?php

namespace WHMCS\Module\Addon\domenytv\Admin\Controllers;

use WHMCS\Module\Addon\domenytv\Api\ResponseController;
use WHMCS\Module\Common\domenytv\Admin\Controllers\Controller;

class AjxController extends Controller
{

    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->response = new ResponseController();
    }

    protected function getJsonPost()
    {
        return json_decode(trim(\file_get_contents('php://input')));
    }

    protected function getRawJsonPost()
    {
        return trim(\file_get_contents('php://input'));
    }


}
