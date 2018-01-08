<?php


namespace App\Acme;


use Ramsey\Uuid\Uuid;

class Model
{
    private $connection;
    private $query;

    public static function ifSet(&$val, $def = null) {
        return isset($val) ? $val : $def;
    }

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

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function deleteById($id, $table)
    {
        return $this
            ->getQuery()
            ->delete($table)
            ->where('id="' . Uuid::fromString($id)->getBytes() . '"')
            ->execute();
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

    public function getRace($raceId)
    {
        $race = $this
            ->getQuery()
            ->select('*')
            ->from('race')
            ->where('id="' . Uuid::fromString($raceId)->getBytes() . '"')
            ->execute()
            ->fetch();
        $race['id'] = Uuid::fromBytes($race['id'])->toString();

        return $race;
    }

    public function insertRace($race)
    {
        return $this
            ->getQuery()
            ->insert('race')
            ->setValue('id', '?')
            ->setValue('name', '?')
            ->setValue('description', '?')
            ->setValue('start_date', '?')
            ->setValue('end_date', '?')
            ->setValue('timezone', '?')
            ->setParameter(0, Uuid::fromString($race['id'])->getBytes())
            ->setParameter(1, $race['name'])
            ->setParameter(2, $race['description'])
            ->setParameter(3, self::ifSet($race['start_date']))
            ->setParameter(4, self::ifSet($race['end_date']))
            ->setParameter(5, self::ifSet($race['timezone']))
            ->execute();
    }

    public function updateRace($race)
    {
        $query = $this
            ->getQuery()
            ->update('race')
            ->set('name', '?')
            ->set('description', '?')
            ->set('start_date', '?')
            ->set('end_date', '?')
            ->set('timezone', '?')
            ->where('id="' . Uuid::fromString($race['id'])->getBytes() . '"')
            ->setParameter(0, $race['name'])
            ->setParameter(1, $race['description'])
            ->setParameter(2, self::ifSet($race['start_date']))
            ->setParameter(3, self::ifSet($race['end_date']))
            ->setParameter(4, self::ifSet($race['timezone']));
        return $query->execute();
    }

    public function deleteRace($raceId)
    {
        return $this->deleteById($raceId, 'race');
    }

    public function getGroups($raceId = null)
    {
        $query = $this
            ->getQuery()
            ->select('*')
            ->from('`group`');
        if ($raceId)
        {
            $query->where('race_id="' . Uuid::fromString($raceId)->getBytes() . '"');
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

    public function insertGroup($group)
    {
        return $this
            ->getQuery()
            ->insert('group')
            ->setValue('id', '?')
            ->setValue('race_id', '?')
            ->setValue('name', '?')
            ->setValue('description', '?')
            ->setValue('course', '?')
            ->setParameter(0, Uuid::fromString($group['id'])->getBytes())
            ->setParameter(1, Uuid::fromString($group['race_id'])->getBytes())
            ->setParameter(2, $group['name'])
            ->setParameter(3, self::ifSet($group['description']))
            ->setParameter(4, self::ifSet($group['course']))
            ->execute();
    }

    public function updateGroup($group)
    {
        return $this
            ->getQuery()
            ->insert('group')
            ->set('race_id', '?')
            ->set('name', '?')
            ->set('description', '?')
            ->set('course', '?')
            ->where('id="' . Uuid::fromString($group['id'])->getBytes() . '"')
            ->setParameter(0, Uuid::fromString($group['race_id'])->getBytes())
            ->setParameter(1, $group['name'])
            ->setParameter(2, self::ifSet($group['description']))
            ->setParameter(3, self::ifSet($group['course']))
            ->execute();
    }

    public function deleteGroup($id)
    {
        return $this->deleteById($id, 'group');
    }

    public function getPersons($groupId = null)
    {
        $res = $this
            ->getQuery()
            ->select('*')
            ->from('`person`');
        if (!is_null($groupId)) {
            $res = $res->where('group_id="' . Uuid::fromString($groupId)->getBytes() . '"');
        }
        $res = $res->execute();
        if (is_int($res)) {
            return [];
        }
        $res = $res->fetchAll();
        foreach ($res as &$person) {
            $person['id'] = Uuid::fromBytes($person['id'])->toString();
            $person['group_id'] = Uuid::fromBytes($person['group_id'])->toString();
        }
        unset($person);

        return $res;
    }

    public function getPerson($id)
    {
        $person = $this
            ->getQuery()
            ->select('*')
            ->from('person')
            ->where('id="' . Uuid::fromString($id)->getBytes() . '"')
            ->execute()
            ->fetch();
        $person['id'] = Uuid::fromBytes($person['id'])->toString();
        $person['group_id'] = Uuid::fromBytes($person['group_id'])->toString();

        return $person;
    }

    public function insertPerson($person)
    {
        return $this
            ->getQuery()
            ->insert('person')
            ->setValue('id', '?')
            ->setValue('group_id', '?')
            ->setValue('name', '?')
            ->setValue('description', '?')
            ->setValue('link', '?')
            ->setValue('bib', '?')
            ->setValue('team', '?')
            ->setValue('start', '?')
            ->setValue('finish', '?')
            ->setValue('result', '?')
            ->setValue('split', '?')
            ->setValue('status', '?')
            ->setParameter(0, Uuid::fromString($person['id'])->getBytes())
            ->setParameter(1, Uuid::fromString($person['group_id'])->getBytes())
            ->setParameter(2, $person['name'])
            ->setParameter(3, self::ifSet($person['description']))
            ->setParameter(4, self::ifSet($person['link']))
            ->setParameter(5, self::ifSet($person['bib']))
            ->setParameter(6, self::ifSet($person['team']))
            ->setParameter(7, self::ifSet($person['start']))
            ->setParameter(8, self::ifSet($person['finish']))
            ->setParameter(9, self::ifSet($person['result']))
            ->setParameter(10, self::ifSet($person['split']))
            ->setParameter(11, self::ifSet($person['status']))
            ->execute();
    }

    public function updatePerson($person)
    {
        return $this
            ->getQuery()
            ->update('person')
            ->set('group_id', '?')
            ->set('name', '?')
            ->set('description', '?')
            ->set('link', '?')
            ->set('bib', '?')
            ->set('team', '?')
            ->set('start', '?')
            ->set('finish', '?')
            ->set('result', '?')
            ->set('split', '?')
            ->set('status', '?')
            ->where('id="' . Uuid::fromString($person['id'])->getBytes() . '"')
            ->setParameter(0, Uuid::fromString($person['group_id'])->getBytes())
            ->setParameter(1, $person['name'])
            ->setParameter(2, self::ifSet($person['description']))
            ->setParameter(3, self::ifSet($person['link']))
            ->setParameter(4, self::ifSet($person['bib']))
            ->setParameter(5, self::ifSet($person['team']))
            ->setParameter(6, self::ifSet($person['start']))
            ->setParameter(7, self::ifSet($person['finish']))
            ->setParameter(8, self::ifSet($person['result']))
            ->setParameter(9, self::ifSet($person['split']))
            ->setParameter(10, self::ifSet($person['status']))
            ->execute();
    }

    public function deletePerson($id)
    {
        return $this->deleteById($id, 'person');
    }
}