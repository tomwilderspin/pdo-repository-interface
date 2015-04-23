<?php
/**
 * QueryStructure.php.
 *
 * built as a immutable linked list item so that a full change
 * history of queries is stored and can be recovered. Useful for
 * debugging queries if logged or transaction monitoring.
 */

namespace PdoDoctrine\DataStructure;


use PdoDoctrine\Entity\EntityInterface;

class QueryStructure extends \SplDoublyLinkedList {

    function __construct(
        EntityInterface $entityObject,
        $databaseName,
        $tableName,
        Array $primaryKeyMap = [],
        Array $fieldNameMap = [],
        Array $conditionList = [],
        Array $resultSet = [],
        Array $queryMetaData = []
    ) {
        $structure = array(
            'entityObject'  => $entityObject,
            'databaseName'  => $databaseName,
            'tableName'     => $tableName,
            'primaryKeyMap' => $primaryKeyMap,
            'fieldNameMap'  => $fieldNameMap,
            'conditionList' => $conditionList,
            'resultSet'     => $resultSet,
            'queryMetaData' => $queryMetaData,
        );

        $this->setIteratorMode(
            \SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP
        );

        $this->add(0, $structure);
        $this->rewind();
    }

    public function resetQueryStructure()
    {
        if ($this->count() > 1) {
            $structure = $this->offsetGet(0);
            $this->push($structure);
            $this->rewind();
        }
    }

    public function addQueryMetaData($queryString = '')
    {
        $this->mergeIntoList('queryMetaData', [
            'queryString' => $queryString,
        ]);

        return $this;
    }

    public function addQueryResultSet(Array $resultSet)
    {
        $this->mergeIntoList('resultSet', $resultSet);

        return $this;
    }

    public function addQueryConditions(Array $conditions)
    {
        $this->mergeIntoList('conditionList', $conditions);

        return $this;
    }

    public function addQueryCondition($field, $value, $operation = '=')
    {
        $this->mergeIntoList(
            'conditionList',
            [
                'field'     => $field,
                'operation' => $operation,
                'value'     => $value,
            ]
        );

        return $this;
    }

    public function addEntityClass(EntityInterface $entity)
    {
        $structure = $this->current();

        $structure['entityObject'] = $entity;

        $this->push($structure);
        $this->rewind();

        return $this;
    }

    public function setEntityValue($methodName, $value)
    {
        $structure = $this->current();
        $method = 'set'.ucwords($methodName);

        if (!method_exists($structure['entityObject'], $method)) {
            //todo add exception
        }
        $structure['entityObject']->$method($value);

        $this->push($structure);
        $this->rewind();

        return $this;
    }

    public function getResultSet()
    {
        return $this->getItemFromStructure('resultSet');
    }

    /**
     * @return \PdoDoctrine\Entity\EntityInterface
     */
    public function getEntityClass()
    {
        return clone $this->getItemFromStructure('entityObject');
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->getItemFromStructure('databaseName');
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->getItemFromStructure('tableName');
    }

    /**
     * @return array
     */
    public function getPrimaryKeyMap()
    {
        return $this->getItemFromStructure('primaryKeyMap');
    }

    /**
     * @return mixed
     */
    public function getFieldNameMap()
    {
        return $this->getItemFromStructure('fieldNameMap');
    }

    /**
     * @return mixed
     */
    public function getQueryMetaData()
    {
        return $this->getItemFromStructure('queryMetaData');
    }

    /**
     * @return mixed
     */
    public function getConditionList()
    {
        return $this->getItemFromStructure('conditionList');
    }

    public function getFieldNameMapWithPrimary()
    {
        return array_merge($this->getFieldNameMap(), $this->getPrimaryKeyMap());
    }

    private function mergeIntoList($itemKey, Array $values)
    {
        $structure = array_merge_recursive(
            $this->current(),
            [ $itemKey => $values ]
        );
        $this->push($structure);
        $this->rewind();
    }

    private function getItemFromStructure($itemKey)
    {
        $structure  = $this->current();
        return $structure[$itemKey];
    }
}