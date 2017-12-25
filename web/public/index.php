<?php

include '../app/vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
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
$app = new \App\Acme\App($db->getConnection(), $configuration);
$app->run();
