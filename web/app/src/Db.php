<?php

namespace App\Acme;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;

class Db
{
    public $connectionOptions;
    private $connection;

    public function __construct($options)
    {
        $this->connectionOptions = $options;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            try {
                $config = new Configuration();
                $this->connection = DriverManager::getConnection($this->connectionOptions, $config);
            } catch (DBALException $e) {}
        }
        return $this->connection;
    }

}