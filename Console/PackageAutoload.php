<?php

spl_autoload_register(function ($res) {
    $res = str_replace(['\\', '/'], '/', $res);
    $nameArr = explode('/', $res);
    $firstName = array_shift($nameArr);
    !$firstName && $firstName = array_shift($nameArr);
    $file = null;
    if ($firstName == 'App') {
        $file = __DIR__ . '/../App/' . implode('/', $nameArr) . '.php';
    } elseif ($firstName == 'Yonna') {
        $file = __DIR__ . '/../library/' . implode('/', $nameArr) . '.php';
    } elseif (in_array($firstName, ['POption', 'Dotenv', 'Symfony', 'Seclib', 'AmqpLib', 'GuzzleHttp', 'Psr', 'Workerman'])) {
        $file = __DIR__ . '/../library/Plugins/' . $res . '.php';
    }
    if (is_file($file)) {
        require($file);
    }
});
