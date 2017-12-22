<?php

namespace App\Acme;


use App\Acme\Controllers\Race;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;

class App
{
    public function main()
    {
        $app = new \Slim\App;
        $app->get('/races', Race::class . ':getAll');

        try {
            $app->run();
        } catch (MethodNotAllowedException $e) {
        } catch (NotFoundException $e) {
        } catch (\Exception $e) {
        }
    }
}
