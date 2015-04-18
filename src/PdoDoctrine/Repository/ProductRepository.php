<?php
/**
 * ProductRepository.php.
 */

namespace PdoDoctrine\Repository;


use PdoDoctrine\Entity\EntityInterface;

class ProductRepository extends AbstractRepository {

    public function getById($id)
    {
        $queryStructure = $this->newQueryStructure();

        $queryStructure
            ->addQueryCondition('id', $id)
            ->addQueryCondition('status_value', 50);

        return $this->select($queryStructure)->getResultSet();
    }

    public function updateById($id, EntityInterface $entity)
    {
        $queryStructure = $this->newQueryStructure();

        // todo allow for multiple entity items in query structure.
    }

}