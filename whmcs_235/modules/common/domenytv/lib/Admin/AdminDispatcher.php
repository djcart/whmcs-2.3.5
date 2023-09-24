<?php

namespace WHMCS\Module\Common\domenytv\Admin;

use WHMCS\Module\Common\domenytv\Lang;
use WHMCS\Module\Common\domenytv\ApiResponse;

/**
 * Admin dispatcher
 */
class AdminDispatcher
{

    public static $params;
    public static $lang;

    public function dispatch($action, $parameters)
    {

        self::$params = $parameters;
        self::$lang = $parameters['ErrorsLang'];

        $explodedParts = explode('_', $action);

        if (count($explodedParts) > 1) {

            $className = \ucfirst(array_shift($explodedParts)) . 'Controller';

            $classPath = static::$classPath . preg_split('/(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY)[0];
            $class = $classPath . '\\' . $className;

            if (class_exists($class)) {
                $controller = new $class();
                $action = join('_', $explodedParts);

                if (is_callable([$controller, $action])) {
                    $response = $controller->$action($parameters);

                    /* WHMCS probably check if response is_array so array object has to be converted to array */
                    if (is_object($response) && get_class($response) == ApiResponse::class) {
                        $response = $response->toArray();
                    }

                    return $response;
                }
            }



            header("HTTP/1.0 404 Not Found");
            echo '<p>' . Lang::t('wrong_request') . '</p>';
            die();
        }
    }

}
