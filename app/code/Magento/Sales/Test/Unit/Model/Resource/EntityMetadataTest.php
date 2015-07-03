<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Resource;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class EntityMetadataTest
 */
class EntityMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\EntityMetadata
     */
    protected $entityMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\AbstractModel
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $this->getMock(
            'Magento\Sales\Model\AbstractModel',
            [],
            [],
            '',
            false
        );
        $this->resource = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            "",
            false,
            false,
            true,
            ['getReadConnection', 'getMainTable']
        );
        $this->connection = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            "",
            false,
            false
        );
        $this->model->expects($this->any())->method('getResource')->willReturn($this->resource);
        $this->resource->expects($this->any())->method('getReadConnection')->willReturn($this->connection);
        $this->entityMetadata = $objectManager->getObject('Magento\Sales\Model\Resource\EntityMetadata');
    }

    public function testGetFields()
    {
        $mainTable = 'main_table';
        $expectedDescribedTable = 'described_table';
        $this->resource->expects($this->any())->method('getMainTable')->willReturn($mainTable);
        $this->connection->expects($this->once())->method('describeTable')->with($mainTable)->willReturn(
            $expectedDescribedTable
        );
        $this->assertEquals($expectedDescribedTable, $this->entityMetadata->getFields($this->model));
        //get from cached
        $this->connection->expects($this->never())->method('describeTable');
        $this->assertEquals($expectedDescribedTable, $this->entityMetadata->getFields($this->model));
    }
}
