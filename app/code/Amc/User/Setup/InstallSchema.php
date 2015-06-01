<?php

namespace Amc\User\Setup;

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

        $columns = [
            'user_fathername' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'User Fathers Name'
            ],
            'user_position' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'User Position'
            ],
            'user_phone' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'User Phone'
            ],
            'user_country' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'User Country'
            ],
            'user_postcode' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'User Postcode'
            ],
            'user_city' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'User City'
            ],
            'user_street' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'User Street'
            ],
            'user_dob' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'User Date of Birth'
            ],
            'user_license_valid_date' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'User License Valid Date'
            ],
        ];

        foreach ($columns as $columnName => $definition) {
            $setup->getConnection()->addColumn(
                $setup->getTable('admin_user'),
                $columnName,
                $definition
            );
        }

        $setup->endSetup();
    }
}
