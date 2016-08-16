<?php

namespace Amc\Consultation\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->fixForeignKeys($setup);

        $setup->getConnection()->addColumn(
            $setup->getTable('amc_consultation_entity'),
            'order_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'after'    => 'user_id'
            ]
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName('amc_consultation_entity', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amc_consultation_entity'),
            'order_item_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'after'    => 'order_id'
            ]
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName('amc_consultation_entity', 'order_item_id', 'sales_order_item', 'item_id'),
            'order_item_id',
            $setup->getTable('sales_order_item'),
            'item_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->endSetup();
    }

    private function fixForeignKeys(SchemaSetupInterface $setup)
    {
        $fkName = $setup->getFkName('amc_consultation_entity', 'product_id', 'catalog_product_entity', 'entity_id');
        $setup->getConnection()->addForeignKey(
            $fkName,
            $setup->getTable('amc_consultation_entity'),
            'product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );

        $fkName = $setup->getFkName('amc_consultation_entity', 'customer_id', 'customer_entity', 'entity_id');
        $setup->getConnection()->addForeignKey(
            $fkName,
            $setup->getTable('amc_consultation_entity'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );

        $fkName = $setup->getFkName('amc_consultation_entity', 'user_id', 'admin_user', 'user_id');
        $setup->getConnection()->addForeignKey(
            $fkName,
            $setup->getTable('amc_consultation_entity'),
            'user_id',
            $setup->getTable('admin_user'),
            'user_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );
    }
}
