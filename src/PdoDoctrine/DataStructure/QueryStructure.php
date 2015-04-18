<?php
/**
 * QueryStructure.php.
 */

namespace PdoDoctrine\DataStructure;


use PdoDoctrine\Entity\EntityInterface;

class QueryStructure extends \SplDoublyLinkedList {

    function __construct(
        EntityInterface $entityClass,
        $databaseName,
        $tableName,
        Array $primaryKeyMap = array(),
        Array $fieldNameMap = array(),
        Array $conditionList = array(),
        Array $resultSet = array()
    ) {
        $structure = array(
            'entityClass'   => $entityClass,
            'databaseName'  => $databaseName,
            'tableName'     => $tableName,
            'primaryKeyMap' => $primaryKeyMap,
            'fieldNameMap'  => $fieldNameMap,
            'conditionList' => $conditionList,
            'resultSet'     => $resultSet,
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
            array(
                'field'     => $field,
                'operation' => $operation,
                'value'     => $value,
            )
        );

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
        return $this->getItemFromStructure('entityClass');
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
            array($itemKey => $values)
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