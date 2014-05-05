<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_CatalogRule
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer \Magento\Framework\Module\Setup */
$installer = $this;

$tableName = $installer->getTable('catalogrule');
$columnOptions = array(
    'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    'UNSIGNED' => true,
    'NULLABLE' => false,
    'DEFAULT' => 0,
    'COMMENT' => 'Is Rule Enable For Subitems'
);
$installer->getConnection()->addColumn($tableName, 'sub_is_enable', $columnOptions);

$columnOptions = array(
    'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    'LENGTH' => 32,
    'COMMENT' => 'Simple Action For Subitems'
);
$installer->getConnection()->addColumn($tableName, 'sub_simple_action', $columnOptions);

$columnOptions = array(
    'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    'SCALE' => 4,
    'PRECISION' => 12,
    'NULLABLE' => false,
    'DEFAULT' => '0.0000',
    'COMMENT' => 'Discount Amount For Subitems'
);
$installer->getConnection()->addColumn($tableName, 'sub_discount_amount', $columnOptions);