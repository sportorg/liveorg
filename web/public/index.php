<?php

include '../app/vendor/autoload.php';

$debug = true;

$publicDir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'debug' => $debug
    ],
];

$db = new \App\Acme\Db([
    'driver'   => 'pdo_mysql',
    'host'     => 'mysql',
    'port'     => '3306',
    'dbname'   => 'liveorg',
    'user'     => 'root',
    'password' => 'root'
]);

$logger = new \Monolog\Logger('main');
$handler = new \Monolog\Handler\StreamHandler(
    $publicDir . 'logs.log',
    $debug ? \Monolog\Logger::DEBUG : \Monolog\Logger::CRITICAL
);
$logger->pushHandler($handler);

$app = new \App\Acme\App($db, $configuration, $logger);
$app->run();
