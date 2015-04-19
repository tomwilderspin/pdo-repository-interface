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

    private $queryStructure;

    public function __construct(Connection $connection, QueryStructure $queryStructure)
    {
        $this->connection = $connection;
        $this->queryStructure = $queryStructure;
    }

    protected function newQueryStructure()
    {
        $this->queryStructure->resetQueryStructure();

        return $this->queryStructure;
    }

    protected function insert(QueryStructure $queryStructure)
    {
        $query = $this->createInsertQuery(
            $this->connection,
            $this->setFieldsForQuery(
                $this->queryStructure->getEntityClass(),
                'setValue'
            )
        );

        try {
            $query($queryStructure)->execute();
        } catch (DBALException $e) {

            echo $e->getMessage(); #todo change this to app logging method & add update exception handle
        }

        return $queryStructure;
    }

    protected function select(QueryStructure $queryStructure)
    {
        $query = $this->createSelectQuery(
            $this->connection,
            $this->createConditionalExpression()
        );

        $resultBuilder = $this->resultBuilder(
            $this->mapToFields(
                $this->fieldSetMapper(
                    $queryStructure->getFieldNameMapWithPrimary()
                ),
                $queryStructure->getEntityClass()
            )
        );

        try {

            $queryStructure->addQueryResultSet(
                $resultBuilder(
                    $queryStructure->getEntityClass(),
                    $query($queryStructure)->execute() #io to db
                )
            );

        } catch (DBALException $e) {

            echo $e->getMessage(); #todo change this to app logging method & add select exception handle
        }

        return $queryStructure;
    }

    protected function update(QueryStructure $queryStructure)
    {
        $query = $this->createUpdateQuery(
            $this->connection,
            $this->createConditionalExpression(),
            $this->setFieldsForQuery(
                $queryStructure->getEntityClass()
            )
        );

        try {
            $query($queryStructure)->execute();
        } catch (DBALException $e) {

            echo $e->getMessage(); #todo change this to app logging method & add update exception handle
        }

        return $queryStructure;
    }

    private function createInsertQuery(Connection $connection, \Closure $setFieldsForQuery)
    {
        // FYI doctrine only allows single row inserts per query. Not normally a performance bottleneck but GTK.
        return function(QueryStructure $queryStructure) use ($connection, $setFieldsForQuery)
        {
            $query = $connection->createQueryBuilder();

            return $setFieldsForQuery($queryStructure->getFieldNameMap(), $query)
                ->insert($queryStructure->getTableName());
        };
    }


    private function createUpdateQuery(Connection $connection, \Closure $createConditionalExpression, \Closure $setFieldsForQuery)
    {
        return function(QueryStructure $queryStructure) use ($connection, $createConditionalExpression, $setFieldsForQuery) {

            $query = $connection->createQueryBuilder();

            return $setFieldsForQuery($queryStructure->getFieldNameMap(), $query)
                ->update($queryStructure->getTableName())
                ->where($createConditionalExpression($queryStructure->getConditionList(),$query));
        };
    }



    private function setFieldsForQuery(EntityInterface $entity, $setMethod = 'set')
    {
        $setField = function(Array $fieldMap, QueryBuilder $query) use ($entity, $setMethod, &$setField) {

            $key = key($fieldMap);
            $method = 'get'.ucwords(array_shift($fieldMap));
            if(method_exists($entity, $method) && !is_null($entity->$method())) {
                $query->$setMethod($key, $query->createNamedParameter($entity->$method()));
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


    private function mapToFields(\Closure $fieldSetMapper, EntityInterface $entity)
    {
        return function(Array $row) use ($fieldSetMapper, $entity) {
            return $fieldSetMapper($entity, $row);
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