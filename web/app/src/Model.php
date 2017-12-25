<?php


namespace App\Acme;


use Ramsey\Uuid\Uuid;

class Model
{
    private $connection;
    private $query;

    /**
     * Model constructor.
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->query = $connection->createQueryBuilder();
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function newToken()
    {
        $token = Uuid::uuid4();
        $this
            ->getQuery()
            ->insert('token')
            ->setValue('token', '?')
            ->setParameter(0, $token->getBytes())->execute();

        return $token;
    }

    public function updateToken($token)
    {
        return $this
            ->getQuery()
            ->update('token')
            ->set('race_id', '?')
            ->where('token="' . Uuid::fromString($token['token'])->getBytes() . '"')
            ->setParameter(0, Uuid::fromString($token['race_id'])->getBytes())
            ->execute();
    }

    public function getRaceIdByToken($token) {
        $res = $this
            ->getQuery()
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

    public function createRace($race)
    {
        return $this
            ->getQuery()
            ->insert('race')
            ->setValue('id', '?')
            ->setValue('name', '?')
            ->setParameter(0, Uuid::fromString($race['id'])->getBytes())
            ->setParameter(1, $race['name'])
            ->execute();
    }

    public function updateRace($race)
    {
        return $this
            ->getQuery()
            ->update('race')
            ->set('name', '?')
            ->where('id="' . Uuid::fromString($race['id'])->getBytes() . '"')
            ->setParameter(0, $race['name'])
            ->execute();
    }

    public function deleteRace($race_id)
    {
        return $this
            ->getQuery()
            ->delete('race')
            ->where('id="' . Uuid::fromString($race_id)->getBytes() . '"')
            ->execute();
    }

    public function getRaces()
    {
        $res = $this
            ->getQuery()
            ->select('*')
            ->from('race')
            ->execute()
            ->fetchAll();
        foreach ($res as &$race) {
            $race['id'] = Uuid::fromBytes($race['id'])->toString();
        }
        unset($race);

        return $res;
    }

    public function getRace($race_id)
    {
        $race = $this
            ->getQuery()
            ->select('*')
            ->from('race')
            ->where('id="' . Uuid::fromString($race_id)->getBytes() . '"')
            ->execute()
            ->fetch();
        $race['id'] = Uuid::fromBytes($race['id'])->toString();

        return $race;
    }

    public function getGroups($race_id = null)
    {
        $query = $this
            ->getQuery()
            ->select('*')
            ->from('`group`');
        if ($race_id)
        {
            $query->where('race_id="' . Uuid::fromString($race_id)->getBytes() . '"');
        }
        $res = $query->execute();
        if (is_int($res)) {
            return [];
        }
        $res = $res->fetchAll();
        foreach ($res as &$group) {
            $group['id'] = Uuid::fromBytes($group['id'])->toString();
            $group['race_id'] = Uuid::fromBytes($group['race_id'])->toString();
        }
        unset($group);

        return $res;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}