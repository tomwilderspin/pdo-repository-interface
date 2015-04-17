<?php
/**
 * AbstractRepository.php.
 */

namespace PdoDoctrine\Repository;


use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use PdoDoctrine\DataStructure\QueryStructure;
use PdoDoctrine\Entity\EntityInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

abstract class AbstractRepository implements RepositoryInterface
{

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    protected function createQueryStructure()
    {

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

    protected function select(QueryStructure $queryStructure)
    {
        $query = $this->createSelectQuery(
            $this->connection,
            $this->createConditionalExpression()
        );

        $resultBuilder = $this->resultBuilder(
            $this->fieldSetMapper(
                $queryStructure->getFieldNameMapWithPrimary()
            )
        );

        $results = array();

        try {

            $results = $resultBuilder($queryStructure->getEntityClass(), $query($queryStructure)->execute()); #io to db

        } catch (DBALException $e) {

            echo $e->getMessage(); #todo change this to app logging method & add select exception handle
        }

        return $results;
    }

    protected function update(QueryStructure $queryStructure)
    {
        $query = $this->createUpdateQuery(
            $this->connection,
            $this->createConditionalExpression(),
            $this->setUpdateFieldsForQuery(
                $queryStructure->getEntityClass()
            )
        );

        try {
            $query($queryStructure)->execute();
        } catch (DBALException $e) {

            echo $e->getMessage(); #todo change this to app logging method & add update exception handle
        }

        return true;
    }


    private function createUpdateQuery(Connection $connection, \Closure $createConditionalExpression, \Closure $setUpdateFieldsForQuery)
    {
        return function(QueryStructure $queryStructure) use ($connection, $createConditionalExpression, $setUpdateFieldsForQuery) {

            $query = $connection->createQueryBuilder();

            return $setUpdateFieldsForQuery($queryStructure->getFieldNameMap(), $query)
                ->update($queryStructure->getTableName())
                ->where($createConditionalExpression($queryStructure->getConditionList(),$query));
        };
    }



    private function setUpdateFieldsForQuery(EntityInterface $entity)
    {
        $setField = function(Array $fieldMap, QueryBuilder $query) use ($entity, &$setField) {

            $key = key($fieldMap);
            $method = 'get'.ucwords(array_shift($fieldMap));
            if(method_exists($entity, $method)) {
                $query->set($key, $query->createNamedParameter($entity->$method()));
            }

            return empty($fieldMap)?
                $query :
                $setField($fieldMap, $query);
        };

        return $setField;
    }

    private function resultBuilder(\Closure $mapToFields)
    {
        return function(Statement $statement) use ($mapToFields) {
            return array_map($mapToFields,$statement->fetchAll(\PDO::FETCH_ASSOC));
        };
    }


    private function createSelectQuery(Connection $connection, \Closure $createConditionalExpression)
    {
        return function (QueryStructure $queryStructure) use ($connection, $createConditionalExpression) {
            $query = $connection->createQueryBuilder();
            return $query->select(array_keys($queryStructure->getFieldNameMap()))
                ->from($queryStructure->getTableName())
                ->where($createConditionalExpression($queryStructure->getConditionList(), $query));
        };
    }

    private function createConditionalExpression()
    {
        $expressionBuilder = function (Array $conditionList, QueryBuilder $queryBuilder) use (&$expression) {

            $condition = array_shift($conditionList);

            $queryBuilder = $queryBuilder->expr()->andX()->add(
                $queryBuilder->expr()->comparison(
                    $condition['field'],
                    $condition['operation'],
                    $queryBuilder->createNamedParameter($condition['value'])
                )
            );
            return empty($conditionList) ?
                $queryBuilder :
                $expression($conditionList, $queryBuilder);
        };
        return $expressionBuilder;
    }

    private function fieldSetMapper(Array $fieldMap)
    {
        $reducer = function(EntityInterface $entity, Array $values) use ($fieldMap, &$reducer) {

            $key = key($values);
            $value = array_shift($values);

            if (array_key_exists($key, $fieldMap)) {

                $methodName = 'set' . ucwords($fieldMap[$key]);

                $entity->$methodName($value);
            }
            return empty($values) ?
                $entity :
                $reducer($entity, $values);
        };

        return $reducer;
    }

}