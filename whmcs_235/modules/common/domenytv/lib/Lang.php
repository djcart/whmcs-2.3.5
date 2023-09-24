<?php

namespace WHMCS\Module\Common\domenytv;

class Lang
{

    public static $langVars;
    private static $instance;

    private function __construct()
    { }

    private function __clone()
    { }

    public function loadLangArray($langVars)
    {
        self::$langVars = $langVars;
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new Lang();
        }
        return self::$instance;
    }

    public static function t($var)
    {
        if (isset(self::$langVars[$var])) {
            return self::$langVars[$var];
        }

        return 'Missing translation: ' . $var;
    }
}
