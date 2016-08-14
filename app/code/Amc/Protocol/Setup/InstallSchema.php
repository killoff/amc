<?php
namespace Amc\Protocol\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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
        $setup->startSetup();

        /**
         * Create table 'protocol'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('protocol'))
            ->addColumn(
                'protocol_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => false, 'default' => '']
            );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'protocol_rows'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('protocol_rows'))
            ->addColumn(
                'row_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0', 'nullable' => false]
            )
            ->addColumn(
                'protocol_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                'text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                'action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                30,
                ['nullable' => true, 'default' => null]
            )
            ->addIndex(
                $setup->getIdxName('protocol_rows', ['parent_id']),
                ['parent_id']
            )
            ->addIndex(
                $setup->getIdxName('protocol_rows', ['protocol_id']),
                ['protocol_id']
            )
            ->addForeignKey(
                $setup->getFkName('protocol_rows', 'protocol_id', 'protocol', 'protocol_id'),
                'protocol_id',
                $setup->getTable('protocol'),
                'protocol_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
        ;
        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
