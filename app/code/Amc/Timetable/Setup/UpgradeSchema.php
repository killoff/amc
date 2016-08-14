<?php

namespace Amc\Timetable\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * @todo: remove this class and keep only InstallSchema (does the same)
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;


    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(SalesSetupFactory $salesSetupFactory)
    {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

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

        $connection = $setup->getConnection();
        $connection->addColumn($installer->getTable('amc_timetable_order_event'), 'uuid', 'VARCHAR(50) NOT NULL AFTER room_id');
        $connection->addIndex(
            $installer->getTable('amc_timetable_order_event'),
            'timetable_order_event_uuid',
            'uuid',
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
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

        $installer->endSetup();
    }
}
