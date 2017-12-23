<?php

include '../app/vendor/autoload.php';

$db = new \App\Acme\Db([
    'driver'   => 'pdo_mysql',
    'host'     => 'mysql',
    'port'     => '3306',
    'dbname'   => 'liveorg',
    'user'     => 'root',
    'password' => 'root'
]);
$app = new \App\Acme\App($db->getConnection());
$app->run();
