<?php

namespace Amc\Timetable\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
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
    }
}
