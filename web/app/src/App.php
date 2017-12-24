<?php

namespace App\Acme;


use Ramsey\Uuid\Uuid;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

class App
{
    private $connection;
    private $query;
    private $slim;

    /**
     * App constructor.
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct($connection)
    {
        $this->slim = new \Slim\App();
        $this->connection = $connection;
        $this->query = $connection->createQueryBuilder();
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

                    $app->query
                        ->update('token')
                        ->set('race_id', '?')
                        ->where('token="' . Uuid::fromString($args['token'])->getBytes() . '"')
                        ->setParameter(0, Uuid::fromString($race['id'])->getBytes())
                        ->execute();

                    $app->query
                        ->insert('race')
                        ->setValue('id', '?')
                        ->setValue('name', '?')
                        ->setParameter(0, Uuid::fromString($race['id'])->getBytes())
                        ->setParameter(1, $race['name'])
                        ->execute();
                    return $response->withJson($race, 201);
                });

                /** @var \Slim\App $this */
                $this->put('/race', function (Request $request, Response $response, $args) use ($app) {
                    $race = $request->getParsedBody();
                    if ($race['id'] != $app->getRaceIdByToken($args['token']))
                    {
                        return $response->withStatus(403);
                    }
                    $app->query
                        ->update('race')
                        ->set('name', '?')
                        ->where('id="' . Uuid::fromString($race['id'])->getBytes() . '"')
                        ->setParameter(0, $race['name'])
                        ->execute();
                    return $response->withJson($race, 201);
                });

                /** @var \Slim\App $this */
                $this->delete('/race/{race_id}', function (Request $request, Response $response, $args) use ($app) {
                    if ($args['race_id'] != $app->getRaceIdByToken($args['token']))
                    {
                        return $response->withStatus(403);
                    }
                    $app->query
                        ->delete('race')
                        ->where('id="' . Uuid::fromString($args['race_id'])->getBytes() . '"')
                        ->execute();
                    return $response->withStatus(204);
                });
            });

            // Without token

            /** @var \Slim\App $this */
            $this->get('/token', function (Request $request, Response $response, $args) use ($app) {
                $token = Uuid::uuid4();
                $app->query
                    ->insert('token')
                    ->setValue('token', '?')
                    ->setParameter(0, $token->getBytes())->execute();
                return $response->withJson(['token' => $token->toString()]);
            });

            /** @var \Slim\App $this */
            $this->get('/races', function (Request $request, Response $response, $args) use ($app) {
                $res = $app->query
                    ->select('*')
                    ->from('race')
                    ->execute()
                    ->fetchAll();
                foreach ($res as &$race) {
                    $race['id'] = Uuid::fromBytes($race['id'])->toString();
                }
                unset($race);
                return $response->withJson($res);
            });

            /** @var \Slim\App $this */
            $this->get('/race/{race_id}', function (Request $request, Response $response, $args) use ($app) {
                $race = $app->query
                    ->select('*')
                    ->from('race')
                    ->where('id="' . Uuid::fromString($args['race_id'])->getBytes() . '"')
                    ->execute()
                    ->fetch();
                $race['id'] = Uuid::fromBytes($race['id'])->toString();
                return $response->withJson($race);
            });

            /** @var \Slim\App $this */
            $this->get('/groups', function (Request $request, Response $response, $args) use ($app) {
                $query = $app->query
                    ->select('*')
                    ->from('group');
                $race_id = $request->getQueryParam('race_id');
                if ($race_id)
                {
                    $query->where('race_id="' . Uuid::fromString($race_id)->getBytes() . '"');
                }
                $res = $query->execute()->fetchAll();
                foreach ($res as &$group) {
                    $group['id'] = Uuid::fromBytes($group['id'])->toString();
                    $group['race_id'] = Uuid::fromBytes($group['race_id'])->toString();
                }
                unset($group);
                return $response->withJson($res);
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

    private function getRaceIdByToken($token) {
        $res = $this->query
            ->select('*')
            ->from('token')
            ->where('token="' . Uuid::fromString($token)->getBytes() . '"')
            ->execute()
            ->fetch();
        if (!$res) {
            return null;
        }
        return Uuid::fromBytes($res['race_id'])->toString();
    }
}
