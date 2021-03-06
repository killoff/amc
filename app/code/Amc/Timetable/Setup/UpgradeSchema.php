<?php

namespace Amc\Timetable\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createOrderEventTable($installer);
        $this->createQueueStatusTable($installer);
        $this->addAttributesToOrder();

        $installer->endSetup();
    }

    private function createOrderEventTable($installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amc_timetable_order_event')
        )->addColumn(
            'event_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'user_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addColumn(
            'start_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false]
        )->addColumn(
            'end_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false]
        )->addColumn(
            'order_item_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addColumn(
            'room_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Room ID'
        )->addForeignKey(
            $installer->getFkName('amc_timetable_order_event', 'user_id', 'admin_user', 'user_id'),
            'user_id',
            $installer->getTable('admin_user'),
            'user_id',
            Table::ACTION_NO_ACTION
        )->addForeignKey(
            $installer->getFkName('amc_timetable_order_event', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amc_timetable_order_event', 'order_item_id', 'sales_order_item', 'item_id'),
            'order_item_id',
            $installer->getTable('sales_order_item'),
            'item_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amc_timetable_order_event', 'room_id', 'amc_clinic_room_entity', 'entity_id'),
            'room_id',
            $installer->getTable('amc_clinic_room_entity'),
            'entity_id',
            Table::ACTION_NO_ACTION
        )->setComment(
            'Order timetable events'
        );
        $installer->getConnection()->createTable($table);
        $connection = $installer->getConnection();
        $connection->addColumn($installer->getTable('amc_timetable_order_event'), 'uuid', 'VARCHAR(50) NOT NULL AFTER room_id');
        $connection->addIndex(
            $installer->getTable('amc_timetable_order_event'),
            'timetable_order_event_uuid',
            'uuid',
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createQueueStatusTable($installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amc_timetable_queue_status')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addColumn(
            'context',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false]
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => false]
        )->addColumn(
            'changed_by',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true]
        )->addColumn(
            'changed_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false]
        )->addForeignKey(
            $installer->getFkName('amc_timetable_queue_status', 'changed_by', 'admin_user', 'user_id'),
            'changed_by',
            $installer->getTable('admin_user'),
            'user_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amc_timetable_queue_status', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName('amc_timetable_queue_status', 'context'),
            'context'
        )->setComment(
            'Timetable queue status'
        );
        $installer->getConnection()->createTable($table);


    }

    private function addAttributesToOrder()
    {
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $this->moduleDataSetup]);
        $salesInstaller->addAttribute(
            'order',
            'timetable_start_at',
            ['type' => 'datetime', 'visible' => false, 'default' => null]
        );
        $salesInstaller->addAttribute(
            'order',
            'timetable_end_at',
            ['type' => 'datetime', 'visible' => false, 'default' => null]
        );
    }

}
