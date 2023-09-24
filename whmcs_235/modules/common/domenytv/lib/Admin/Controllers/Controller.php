<?php

namespace WHMCS\Module\Common\domenytv\Admin\Controllers;

use WHMCS\Module\Common\domenytv\Admin\AdminDispatcher;
use WHMCS\Module\Common\domenytv\Admin\Controllers\ApiController;
use WHMCS\Module\Common\domenytv\Notifications;

class Controller
{

    public $smarty;
    private static $urls = [
        'production' => 'https://www.domeny.tv/regapi/soap.wsdl.xml',
        'test' => 'https://www.domeny.tv/regapi/test/soap.wsdl.xml',
    ];
    protected $api;
    protected $adminArea = false;
    protected $lang;

    public function __construct()
    {
        $this->smarty = new \Smarty;
        $this->smarty->template_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates');

        $login  = !empty(AdminDispatcher::$params['api_login']) ? AdminDispatcher::$params['api_login'] : AdminDispatcher::$params['Username'];
        $password = !empty(AdminDispatcher::$params['api_password']) ? AdminDispatcher::$params['api_password'] : AdminDispatcher::$params['Password'];
        $url = !empty(AdminDispatcher::$params['TestMode']) && AdminDispatcher::$params['TestMode'] === 'on' ? self::$urls['test'] : self::$urls['production'];

        $this->adminArea = strpos($_SERVER['REQUEST_URI'], '/clientarea.php') === false;
        $this->lang = AdminDispatcher::$lang;
        $this->api = new ApiController($url, $login, $password);
    }

    protected static function redirect($url)
    {
        header('Location: ' . $url);
    }

    /**
     * Fetch smarty template
     *
     * @param  string $templateName
     *
     * @return string
     */
    protected function fetch($templateName)
    {
        $notifications = Notifications::getNotifications();
        $this->smarty->assign('notifications', $notifications);
        $this->smarty->assign('template', $templateName);
        return $this->smarty->fetch('template.tpl');
    }

    /**
     * Check whether controller has been run from admin area
     *
     * @return bool
     */
    protected function isAdminArea()
    {
        return $this->adminArea;
    }

    /**
     * Check whether controller has been run from client area
     *
     * @return bool
     */
    protected function isClientArea()
    {
        return !$this->adminArea;
    }

    static function getCsrDomain($csr)
    {
        $csrArray = openssl_csr_get_subject($csr, 1);

        if (isset($csrArray['CN'])) {
            return $csrArray['CN'];
        }

        return null;
    }
}
