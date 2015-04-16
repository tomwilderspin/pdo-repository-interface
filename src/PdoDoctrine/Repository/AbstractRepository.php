<?php
/**
 * AbstractRepository.php.
 */

namespace PdoDoctrine\Repository;


use AtlasCreativeIntegration\Entity\EntityInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

abstract class AbstractRepository implements RepositoryInterface
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $resourceMap;

    public function __construct(Connection $connection, Array $resourceMap)
    {
        $this->connection = $connection;
        $this->resourceMap = $resourceMap;
    }

    protected function getConnection()
    {
        return $this->connection;
    }

    protected function insertSingleEntity(EntityInterface $entity)
    {
        $data = $entity->toArray();

        $fieldMap = $this->resourceMap['fields'];

        $insertValues = [];

        foreach ($fieldMap as $key => $value) {
            if ( in_array($value, array_keys($data))) {
                $insertValues[$key] = $data[$value];
            }
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->insert($this->resourceMap['table'])
            ->values(array_fill_keys(array_keys($insertValues), '?'))
            ->setParameters(array_values($insertValues));

        try{
            $queryBuilder->execute();
        }catch (DBALException $e) {
            echo $e->getMessage(); #todo change this to app logging method
        }

        return $entity;
    }

    protected function selectEntitiesByMultipleFields(Array $fieldValuePairs)
    {
        $entities = [];

        $fields = $this->resourceMap['fields'];

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(array_keys($fields))
            ->from($this->resourceMap['table']);

        foreach( $fieldValuePairs as $field => $value)
        {
            $queryBuilder->andWhere($field.'='.$queryBuilder->createNamedParameter($value));
        }

        try {
            $executedStatement = $queryBuilder->execute();

            $results = $executedStatement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (DBALException $e) {
            echo $e->getMessage(); #todo again change this to app logging method
        }

        // todo refactor out duplicated code in these select methods
        if(!empty($results)) {
            foreach ($results as $rowItem) {

                $entity = new $this->resourceMap['class'];

                array_walk($rowItem,function($value, $key, $fields) use (&$entity) {

                    $setMethod = 'set'.ucwords($fields[$key]);

                    $entity->$setMethod($value);

                }, $fields);

                $entities[] = $entity;
            }
        }

        return $entities;
    }

    protected function selectEntitiesByField($field, $value)
    {
        $results = [];

        $entities = [];

        $fields = $this->resourceMap['fields'];

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(array_keys($this->resourceMap['fields']))
            ->from($this->resourceMap['table'])
            ->where("$field  = ?")
            ->setParameter(0, $value);

        try {
            $executedStatement = $queryBuilder->execute();

            $results = $executedStatement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (DBALException $e) {
            echo $e->getMessage(); #todo again change this to app logging method
        }

        if(!empty($results)) {
            foreach ($results as $rowItem) {

                $entity = new $this->resourceMap['class'];

                array_walk($rowItem,function($value, $key, $fields) use (&$entity) {

                    $setMethod = 'set'.ucwords($fields[$key]);

                    $entity->$setMethod($value);

                }, $fields);

                $entities[] = $entity;
            }
        }

        return $entities;
    }

    protected function selectEntitiesByCondition(Array $conditions, Array $parameters)
    {

        // todo needs a refactor due to duplicated code
        $results = [];

        $entities = [];

        $fields = $this->resourceMap['fields'];

        $queryBuilder = $this->connection->createQueryBuilder();

        $expression = $queryBuilder->expr()->andX();

        array_walk($conditions,function($item, $key) use (&$expression, $queryBuilder){
            $expression->add($queryBuilder->expr()->$key($item,'?'));
        });

        $queryBuilder
            ->select(array_keys($this->resourceMap['fields']))
            ->from($this->resourceMap['table'])
            ->where($expression)
            ->setParameters($parameters);

        try {
            $executedStatement = $queryBuilder->execute();

            $results = $executedStatement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (DBALException $e) {
            echo $e->getMessage(); #todo again change this to app logging method
        }

        if(!empty($results)) {
            foreach ($results as $rowItem) {

                $entity = new $this->resourceMap['class'];

                array_walk($rowItem,function($value, $key, $fields) use (&$entity) {

                    $setMethod = 'set'.ucwords($fields[$key]);

                    $entity->$setMethod($value);

                }, $fields);

                $entities[] = $entity;
            }
        }

        return $entities;
    }



    protected function updateSingleEntity(EntityInterface $entity, Array $conditionFields = [])
    {
        $data = $entity->toArray();

        $fieldMap = $this->resourceMap['fields'];

        $queryBuilder = $this->connection->createQueryBuilder()->update($this->resourceMap['table']);

        if (!empty($conditionFields)) {
            foreach($conditionFields as $field) {
                if (!array_key_exists($fieldMap[$field],$data)) {
                    throw new \Exception('invalid conditional field: '. $field);
                }

                $queryBuilder->andWhere($field.'='.$queryBuilder->createNamedParameter($data[$fieldMap[$field]]));
            }
        } else {

            if (array_key_exists($fieldMap[$this->resourceMap['primary']], $data)) {

                $value = $data[$fieldMap[$this->resourceMap['primary']]];

                $queryBuilder->where($this->resourceMap['primary'].'='.$queryBuilder->createNamedParameter($value));
            }
        }

        foreach ($fieldMap as $key => $value) {
            if ( in_array($value, array_keys($data))) {
                if ($key !== $this->resourceMap['primary']) {
                    $queryBuilder->set($key,$queryBuilder->createNamedParameter($data[$value]));
                }
            }
        }

        try{
            $queryBuilder->execute();
        }catch (DBALException $e) {
            echo $e->getMessage(); #todo change this to app logging method
            exit();
        }

    }

    protected function selectAllEntities()
    {
        $results = [];
        $entities = [];

        $fields = $this->resourceMap['fields'];

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(array_keys($this->resourceMap['fields']))
            ->from($this->resourceMap['table']);

        try {
            $executedStatement = $queryBuilder->execute();

            $results = $executedStatement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (DBALException $e) {
            echo $e->getMessage();
        }

        if(!empty($results)) {
            foreach ($results as $rowItem) {

                $entity = new $this->resourceMap['class'];

                array_walk($rowItem,function($value, $key, $fields) use (&$entity) {

                    $setMethod = 'set'.ucwords($fields[$key]);

                    $entity->$setMethod($value);

                }, $fields);

                $entities[] = $entity;
            }
        }

        return $entities;
    }

}