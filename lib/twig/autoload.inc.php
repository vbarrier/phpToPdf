<?php

// Custom by Nico
spl_autoload_register(function ($class) {
    $prefix = 'Twig';
    $base_dir = __DIR__ . '/src/';
    $prefixLength = strlen($prefix);
    if (0 === strncmp($prefix, $class, $prefixLength)) {
        $file = str_replace('\\', '/', substr($class, $prefixLength));
        $file = realpath($base_dir . (empty($file) ? '' : '/') . $file . '.php');
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
