<?php

namespace Amc\UserSchedule\Setup;

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

        /**
         * Create table 'amc_user_schedule'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amc_user_schedule')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'user_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'User ID'
        )->addColumn(
            'room_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Room ID'
        )->addColumn(
            'start_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Start At'
        )->addColumn(
            'end_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'End At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Created At'
        )->addForeignKey(
            $installer->getFkName('amc_user_schedule', 'user_id', 'admin_user', 'user_id'),
            'user_id',
            $installer->getTable('admin_user'),
            'user_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('amc_user_schedule', 'room_id', 'amc_clinic_room_entity', 'entity_id'),
            'room_id',
            $installer->getTable('amc_clinic_room_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'AMC Ticket Entity'
        );
        $installer->getConnection()->createTable($table);
    }
}
