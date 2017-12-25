<?php

namespace App\Acme;


use Slim\Container;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

class App
{
    private $slim;
    private $connection;
    private $model;

    /**
     * App constructor.
     * @param \Doctrine\DBAL\Connection $connection
     * @param array $configuration
     */
    public function __construct($connection, $configuration = [])
    {
        $container = new Container($configuration);
        $this->slim = new \Slim\App($container);
        $this->connection = $connection;
        $this->model = new Model($connection);
    }

    public function run()
    {
        $app = $this;

        $this->slim->group('/api.v1', function() use ($app) {

            /** @var \Slim\App $this */
            $this->group('/{token}', function() use ($app) {
                /** @var \Slim\App $this */
                $this->post('/race', function (Request $request, Response $response, $args) use ($app) {;
                    $race = $request->getParsedBody();

                    $app->model->updateToken([
                        'token' => $args['token'],
                        'race_id' => $race['id']
                    ]);

                    $app->model->createRace($race);

                    return $response->withJson($race, 201);
                });

                /** @var \Slim\App $this */
                $this->put('/race', function (Request $request, Response $response, $args) use ($app) {
                    $race = $request->getParsedBody();
                    if ($race['id'] != $app->model->getRaceIdByToken($args['token']))
                    {
                        return $response->withStatus(403);
                    }
                    $app->model->updateRace($race);
                    return $response->withJson($race, 201);
                });

                /** @var \Slim\App $this */
                $this->delete('/race/{race_id}', function (Request $request, Response $response, $args) use ($app) {
                    if ($args['race_id'] != $app->model->getRaceIdByToken($args['token']))
                    {
                        return $response->withStatus(403);
                    }
                    $app->model->deleteRace($args['race_id']);
                    return $response->withStatus(204);
                });
            });

            // Without token

            /** @var \Slim\App $this */
            $this->get('/token', function (Request $request, Response $response, $args) use ($app) {
                $token = $app->model->newToken();
                return $response->withJson(['token' => $token->toString()]);
            });

            /** @var \Slim\App $this */
            $this->get('/races', function (Request $request, Response $response, $args) use ($app) {

                return $response->withJson($app->model->getRaces());
            });

            /** @var \Slim\App $this */
            $this->get('/race/{race_id}', function (Request $request, Response $response, $args) use ($app) {
                return $response->withJson($app->model->getRace($args['race_id']));
            });

            /** @var \Slim\App $this */
            $this->get('/groups', function (Request $request, Response $response, $args) use ($app) {
                $race_id = $request->getQueryParam('race_id');
                return $response->withJson($app->model->getGroups($race_id));
            });
        });

        try {
            $this->slim->run();
            $this->connection->close();
        } catch (MethodNotAllowedException $e) {
        } catch (NotFoundException $e) {
        } catch (\Exception $e) {
        }
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
}
