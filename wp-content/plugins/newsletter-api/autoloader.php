<?php

// This piece of code is loaded inside a function
global $tnp_api_autoloader_prefix, $tnp_api_autoloader_prefix_len, $tnp_api_autoloader_base_dir;

$tnp_api_autoloader_prefix = 'TNP\\API\\';
$tnp_api_autoloader_prefix_len = strlen($tnp_api_autoloader_prefix);
$tnp_api_autoloader_base_dir = __DIR__ . '/';

spl_autoload_register(function ($class) {
    global $tnp_api_autoloader_prefix, $tnp_api_autoloader_prefix_len, $tnp_api_autoloader_base_dir;

    if (strncmp($tnp_api_autoloader_prefix, $class, $tnp_api_autoloader_prefix_len) !== 0) {
        return;
    }
    $relative_class = substr($class, $tnp_api_autoloader_prefix_len);
    $file = $tnp_api_autoloader_base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
