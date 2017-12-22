<?php

namespace App\Acme\Controllers;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Race
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, $args) {
        return $response;
    }
}