<?php
namespace Amc\Protocol\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Create table 'protocol_products'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('protocol_products'))
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addColumn(
                'protocol_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0', 'nullable' => false]
            )
            ->addIndex(
                $setup->getIdxName('protocol_products', ['product_id']),
                ['product_id']
            )
            ->addIndex(
                $setup->getIdxName('protocol_products', ['protocol_id']),
                ['protocol_id']
            )
            ->addForeignKey(
                $setup->getFkName('protocol', 'protocol_id', 'protocol_products', 'protocol_id'),
                'protocol_id',
                $setup->getTable('protocol'),
                'protocol_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName('catalog_product', 'entity_id', 'protocol_products', 'product_id'),
                'product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
        ;
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'protocol_products'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('protocol_hypertext'))
            ->addColumn(
                'protocol_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false]
            )
            ->addForeignKey(
                $setup->getFkName('protocol', 'protocol_id', 'protocol_hypertext', 'protocol_id'),
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
