<?php
/**
 * QueryStructure.php.
 */

namespace PdoDoctrine\DataStructure;


use PdoDoctrine\Entity\EntityInterface;

class QueryStructure {

    private $entityClass;
    private $databaseName;
    private $tableName;
    private $primaryKeyMap;
    private $fieldNameMap;
    private $conditionList;

    function __construct(
        EntityInterface $entityClass,
        $databaseName,
        $tableName,
        Array $primaryKeyMap = array(),
        Array $fieldNameMap = array(),
        Array $conditionList = array()
    ) {
        $this->entityClass = $entityClass;
        $this->databaseName = $databaseName;
        $this->tableName = $tableName;
        $this->primaryKeyMap = $primaryKeyMap;
        $this->fieldNameMap = $fieldNameMap;
        $this->conditionList = $conditionList;
    }

    /**
     * @return array
     */
    public function getConditionParametersList()
    {
        return $this->conditionParametersList;
    }

    /**
     * @return \PdoDoctrine\Entity\EntityInterface
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return array
     */
    public function getPrimaryKeyMap()
    {
        return $this->primaryKeyMap;
    }

    /**
     * @return mixed
     */
    public function getFieldNameMap()
    {
        return $this->fieldNameMap;
    }

    /**
     * @return mixed
     */
    public function getConditionList()
    {
        return $this->conditionList;
    }

    public function getFieldNameMapWithPrimary()
    {
        return array_merge($this->getFieldNameMap(), $this->getPrimaryKeyMap());
    }
}