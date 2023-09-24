<?php

namespace WHMCS\Module\Common\domenytv;

class Notifications
{

    public static $containerName = 'domeny_tv_notifications';

    private function __construct()
    {

    }

    public static function getNotifications()
    {
        if (!isset($_SESSION[self::$containerName])) {
            $_SESSION[self::$containerName] = array();
        }

        $notifications = $_SESSION[self::$containerName];
        $_SESSION[self::$containerName] = array();

        return $notifications;
    }

    public static function setNotifcation($msg, $type)
    {
        $notification = array('type' => $type, 'message' => $msg);
        $notifications = self::getNotifications();
        if (!$notifications) {
            $notifications = array();
        }

        $notifications[] = $notification;

        $_SESSION[self::$containerName] = $notifications;
    }

    public static function error($msg)
    {
        self::setNotifcation($msg, 'error');
    }

    public static function success($msg)
    {
        self::setNotifcation($msg, 'success');
    }

}
