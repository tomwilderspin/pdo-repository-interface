<?php
/**
 * ProductRepositoryTest.php.
 */

namespace Test\Unit\PdoDoctrine\Repository;


use PdoDoctrine\DataStructure\QueryStructure;
use PdoDoctrine\Entity\ProductEntity;
use PdoDoctrine\Repository\ProductRepository;

class ProductRepositoryTest extends \PHPUnit_Framework_TestCase {

    private $productRepository;

    private $mockConnection;

    private $mockStatement; #will need this later..

    private $queryStructure;


    public function setUp()
    {
        $this->mockConnection = \Mockery::mock(
            'Doctrine\DBAL\Connection'
        );

        $this->queryStructure = new QueryStructure(
            new ProductEntity(),
            'somedb',
            'sometable'
        );

        $this->productRepository = new ProductRepository(
            $this->mockConnection,
            $this->queryStructure
        );
    }

}