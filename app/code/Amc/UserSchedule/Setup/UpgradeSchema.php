<?php

namespace Amc\UserSchedule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

        $connection = $setup->getConnection();
        $connection->dropColumn($setup->getTable('amc_user_schedule'), 'start_at');
        $connection->dropColumn($setup->getTable('amc_user_schedule'), 'end_at');
        $connection->addColumn($setup->getTable('amc_user_schedule'), 'start_at', 'DATETIME AFTER room_id');
        $connection->addColumn($setup->getTable('amc_user_schedule'), 'end_at', 'DATETIME AFTER start_at');

        $setup->endSetup();
    }
}
