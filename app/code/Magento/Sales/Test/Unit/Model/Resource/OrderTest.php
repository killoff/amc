<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Resource;

use \Magento\Sales\Model\Resource\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class OrderTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order
     */
    protected $resource;
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;
    /**
     * @var \Magento\SalesSequence\Model\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesSequenceManagerMock;
    /**
     * @var \Magento\SalesSequence\Model\Sequence|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesSequenceMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;
    /**
     * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeGroupMock;
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;
    /**
     * @var \Magento\Sales\Model\Resource\EntitySnapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var \Magento\Sales\Model\Resource\EntityRelationComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationCompositeMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\ObjectRelationProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRelationProcessorMock;
    /**
     * Mock class dependencies
     */
    public function setUp()
    {
        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeGroupMock = $this->getMock('Magento\Store\Model\Group', [], [], '', false);
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [
                'describeTable',
                'insert',
                'lastInsertId',
                'beginTransaction',
                'rollback',
                'commit',
                'quoteInto',
                'update'
            ],
            [],
            '',
            false
        );
        $this->salesSequenceManagerMock = $this->getMock(
            'Magento\SalesSequence\Model\Manager',
            [],
            [],
            '',
            false
        );
        $this->salesSequenceMock = $this->getMock('Magento\SalesSequence\Model\Sequence', [], [], '', false);
        $this->entitySnapshotMock = $this->getMock(
            'Magento\Sales\Model\Resource\EntitySnapshot',
            [],
            [],
            '',
            false
        );
        $this->relationCompositeMock = $this->getMock(
            'Magento\Sales\Model\Resource\EntityRelationComposite',
            [],
            [],
            '',
            false
        );
        $this->objectRelationProcessorMock = $this->getMock(
            'Magento\Framework\Model\Resource\Db\ObjectRelationProcessor',
            [],
            [],
            '',
            false
        );
        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->objectRelationProcessorMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->resource = $objectManager->getObject(
            'Magento\Sales\Model\Resource\Order',
            [
                'context' => $contextMock,
                'sequenceManager' => $this->salesSequenceManagerMock,
                'entitySnapshot' => $this->entitySnapshotMock,
                'entityRelationComposite' => $this->relationCompositeMock
            ]
        );
    }

    public function testSave()
    {

        $this->orderMock->expects($this->once())
            ->method('validateBeforeSave')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('beforeSave')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('isSaveAllowed')
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getEntityType')
            ->willReturn('order');
        $this->orderMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getGroup')
            ->willReturn($this->storeGroupMock);
        $this->storeGroupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn(1);
        $this->salesSequenceManagerMock->expects($this->once())
            ->method('getSequence')
            ->with('order', 1)
            ->willReturn($this->salesSequenceMock);
        $this->salesSequenceMock->expects($this->once())
            ->method('getNextValue')
            ->willReturn('10000001');
        $this->orderMock->expects($this->once())
            ->method('setIncrementId')
            ->with('10000001')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('getData')
            ->willReturn(['increment_id' => '10000001']);
        $this->objectRelationProcessorMock->expects($this->once())
            ->method('validateDataIntegrity')
            ->with(null, ['increment_id' => '10000001']);
        $this->relationCompositeMock->expects($this->once())
            ->method('processRelations')
            ->with($this->orderMock);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapterMock);
        $this->adapterMock->expects($this->any())
            ->method('quoteInto');
        $this->adapterMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->adapterMock->expects($this->any())
            ->method('update');
        $this->adapterMock->expects($this->any())
            ->method('lastInsertId');
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->orderMock)
            ->will($this->returnValue(true));
        $this->resource->save($this->orderMock);
    }
}
