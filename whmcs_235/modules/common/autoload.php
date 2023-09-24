<?php

$namespaceMap = array(
    '\\WHMCS\\Module\\Addon\\domenytv' => 'modules/addons/domenytv/lib',
    '\\WHMCS\\Module\\Common\\domenytv' => 'modules/common/domenytv/lib',
    '\\WHMCS\\Module\\Common\\helpers' => 'modules/common/helpers',
    '\\WHMCS\\Module\\Registrar\\domenytv' => 'modules/registrars/domenytv/lib',
    '\\WHMCS\\\Module\\Addon\\domenytv\\Installer' => 'modules/addons/domenytv/lib'

);

spl_autoload_register(function ($class_name) use ($namespaceMap) {


    if ($class_name[0] != '\\') {
        $class_name = '\\' . $class_name;
    }

    foreach ($namespaceMap as $namespace => $dir) {
        if (strpos($class_name, $namespace) === 0) {
            $replaces = array(
                $namespace => $dir,
                '\\' => DIRECTORY_SEPARATOR,
                '/' => DIRECTORY_SEPARATOR
            );
            $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
            str_replace(array_keys($replaces), array_values($replaces), $class_name) . '.php';
            $file_path = realpath($path);

            if ($file_path) {
                include($file_path);
            }

            return;
        }
    }
});
