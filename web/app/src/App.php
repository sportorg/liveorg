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
    private $logger;

    /**
     * App constructor.
     * @param Db $db
     * @param array $configuration
     * @param \Monolog\Logger $logger
     */
    public function __construct(Db $db, $configuration = [], $logger = null)
    {
        $container = new Container($configuration);
        $this->slim = new \Slim\App($container);
        $this->connection = $db->getConnection();
        $this->logger = $logger;
        $this->model = new Model($this->connection);
    }

    public function run()
    {
        $app = $this;

        // Token
        $this->slim->get('/api.v1/token', function (Request $request, Response $response, $args) use ($app) {
            $token = $app->model->newToken();
            $app->logger->debug('New token');
            return $response->withJson(['token' => $token->toString()]);
        });

        // Race
        $this->slim->get('/api.v1/races', function (Request $request, Response $response, $args) use ($app) {

            return $response->withJson($app->model->getRaces());
        });

        $this->slim->get('/api.v1/race/{race_id}', function (Request $request, Response $response, $args) use ($app) {
            return $response->withJson($app->model->getRace($args['race_id']));
        });

        $this->slim->post('/api.v1/{token}/race', function (Request $request, Response $response, $args) use ($app) {;
            $race = $request->getParsedBody();

            $app->model->updateToken([
                'token' => $args['token'],
                'race_id' => $race['id']
            ]);

            $app->model->createRace($race);

            return $response->withJson($race, 201);
        });

        $this->slim->put('/api.v1/{token}/race', function (Request $request, Response $response, $args) use ($app) {
            $race = $request->getParsedBody();
            if ($race['id'] != $app->model->getRaceIdByToken($args['token']))
            {
                return $response->withStatus(403);
            }
            $app->model->updateRace($race);
            return $response->withJson($race, 201);
        });

        $this->slim->delete('/api.v1/{token}/race/{race_id}', function (Request $request, Response $response, $args) use ($app) {
            if ($args['race_id'] != $app->model->getRaceIdByToken($args['token']))
            {
                return $response->withStatus(403);
            }
            $app->model->deleteRace($args['race_id']);
            return $response->withStatus(204);
        });

        // Group
        $this->slim->get('/api.v1/groups', function (Request $request, Response $response, $args) use ($app) {
            $race_id = $request->getQueryParam('race_id');
            return $response->withJson($app->model->getGroups($race_id));
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

    /**
     * @return \Monolog\Logger|null
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
