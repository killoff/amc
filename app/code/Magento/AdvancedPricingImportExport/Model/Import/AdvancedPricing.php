<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;

/**
 * Class AdvancedPricing
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricing extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const VALUE_ALL_GROUPS = 'ALL GROUPS';

    const VALUE_ALL_WEBSITES = 'All Websites';

    const COL_SKU = 'sku';

    const COL_TIER_PRICE_WEBSITE = 'tier_price_website';

    const COL_TIER_PRICE_CUSTOMER_GROUP = 'tier_price_customer_group';

    const COL_TIER_PRICE_QTY = 'tier_price_qty';

    const COL_TIER_PRICE = 'tier_price';

    const COL_GROUP_PRICE_WEBSITE = 'group_price_website';

    const COL_GROUP_PRICE_CUSTOMER_GROUP = 'group_price_customer_group';

    const COL_GROUP_PRICE = 'group_price';

    const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';

    const TABLE_GROUPED_PRICE = 'catalog_product_entity_group_price';

    const DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE = '0';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)',
        ValidatorInterface::ERROR_SKU_IS_EMPTY => 'SKU is empty',
        ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group is invalid',
        ValidatorInterface::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete',
        ValidatorInterface::ERROR_INVALID_GROUP_PRICE_SITE => 'Group Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_GROUP_PRICE_GROUP => 'Group Price customer group is invalid',
        ValidatorInterface::ERROR_GROUP_PRICE_DATA_INCOMPLETE => 'Group Price data is incomplete',
    ];

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $_storeResolver;

    /**
     * @var ImportProduct
     */
    protected $_importProduct;

    /**
     * @var AdvancedPricing\Validator
     */
    protected $_validator;

    /**
     * @var array
     */
    protected $_cachedSkuToDelete;

    /**
     * @var array
     */
    protected $_oldSkus;

    /**
     * @var AdvancedPricing\Validator\Website
     */
    protected $websiteValidator;

    /**
     * @var AdvancedPricing\Validator\GroupPrice
     */
    protected $groupPriceValidator;

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\ImportExport\Model\Resource\Import\Data $importData
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory $resourceFactory
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param ImportProduct\StoreResolver $storeResolver
     * @param ImportProduct $importProduct
     * @param AdvancedPricing\Validator $validator
     * @param AdvancedPricing\Validator\Website $websiteValidator
     * @param AdvancedPricing\Validator\GroupPrice $groupPriceValidator
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\ImportExport\Model\Resource\Import\Data $importData,
        \Magento\Framework\App\Resource $resource,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory $resourceFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        ImportProduct $importProduct,
        AdvancedPricing\Validator $validator,
        AdvancedPricing\Validator\Website $websiteValidator,
        AdvancedPricing\Validator\GroupPrice $groupPriceValidator
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection('write');
        $this->_resourceFactory = $resourceFactory;
        $this->_productModel = $productModel;
        $this->_catalogData = $catalogData;
        $this->_storeResolver = $storeResolver;
        $this->_importProduct = $importProduct;
        $this->_validator = $validator;
        $this->_oldSkus = $this->retrieveOldSkus();
        $this->websiteValidator = $websiteValidator;
        $this->groupPriceValidator = $groupPriceValidator;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'advanced_pricing';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $sku = false;
        if (isset($this->_validatedRows[$rowNum])) {
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::COL_SKU])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                return false;
            }
            return true;
        }
        if (!$this->_validator->isValid($rowData)) {
            foreach ($this->_validator->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }
        if (isset($rowData[self::COL_SKU])) {
            $sku = $rowData[self::COL_SKU];
        }
        if (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        }
        return !isset($this->_invalidRows[$rowNum]);
    }

    /**
     * Create Advanced price data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveAdvancedPricing();
        }

        return true;
    }

    /**
     * Save advanced pricing
     *
     * @return void
     */
    public function saveAdvancedPricing()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $tierPrices = [];
            $groupPrices = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    continue;
                }
                $rowSku = $rowData[self::COL_SKU];
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]
                        ),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE],
                        'website_id' => $this->getWebsiteId($rowData[self::COL_TIER_PRICE_WEBSITE])
                    ];
                }
                if (!empty($rowData[self::COL_GROUP_PRICE_WEBSITE])) {
                    $groupPrices[$rowSku][] = [
                        'all_groups' => self::DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_GROUP_PRICE_CUSTOMER_GROUP]
                        ),
                        'value' => $rowData[self::COL_GROUP_PRICE],
                        'website_id' => $this->getWebSiteId($rowData[self::COL_GROUP_PRICE_WEBSITE])
                    ];
                }
            }
            $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE)
                ->saveProductPrices($groupPrices, self::TABLE_GROUPED_PRICE);
        }
    }

    /**
     * Deletes Advanced price data from raw data.
     *
     * @return void
     */
    public function deleteAdvancedPricing()
    {
        $this->_cachedSkuToDelete = null;
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum)) {
                    $rowSku = $rowData[self::COL_SKU];
                    $listSku[] = $rowSku;
                }
            }
        }
        if ($listSku) {
            $this->deleteProductTierAndGroupPrices(array_unique($listSku), self::TABLE_GROUPED_PRICE)
                ->deleteProductTierAndGroupPrices(array_unique($listSku), self::TABLE_TIER_PRICE);
        }
    }

    /**
     * Replace advanced pricing
     *
     * @return bool
     */
    public function replaceAdvancedPricing()
    {
    }

    /**
     * Save product prices.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveProductPrices(array $priceData, $table)
    {
        if ($priceData) {
            $tableName = $this->_resourceFactory->create()->getTable($table);
            $priceIn = [];
            foreach ($priceData as $sku => $priceRows) {
                $productId = $this->_oldSkus[$sku];
                foreach ($priceRows as $row) {
                    $row['entity_id'] = $productId;
                    $priceIn[] = $row;
                }
            }
            if ($priceIn) {
                $this->_connection->insertOnDuplicate($tableName, $priceIn, ['value']);
            }
        }
        return $this;
    }

    /**
     * Deletes tier prices and group prices.
     *
     * @param array $listSku
     * @param string $tableName
     * @return $this
     */
    protected function deleteProductTierAndGroupPrices(array $listSku, $tableName)
    {
        if ($tableName && $listSku) {
            if (!$this->_cachedSkuToDelete) {
                $this->_cachedSkuToDelete = $this->_connection->fetchCol(
                    $this->_connection->select()
                    ->from($this->_connection->getTableName('catalog_product_entity'), 'entity_id')
                    ->where('sku IN (?)', $listSku)
                );
            }
            if ($this->_cachedSkuToDelete) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $this->_cachedSkuToDelete)
                );
            } else {
                $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, 0);
                return false;
            }
        }
        return $this;
    }

    /**
     * Get website id by code
     *
     * @param string $websiteCode
     * @return array|int|string
     */
    protected function getWebSiteId($websiteCode)
    {
        $result = $websiteCode == $this->websiteValidator->getAllWebsitesValue() ||
        $this->_catalogData->isPriceGlobal() ? 0 : $this->_storeResolver->getWebsiteCodeToId($websiteCode);
        return $result;
    }

    /**
     * Get customer group id
     *
     * @param string $customerGroup
     * @return int
     */
    protected function getCustomerGroupId($customerGroup)
    {
        $customerGroups = $this->groupPriceValidator->getCustomerGroups();
        return $customerGroup == self::VALUE_ALL_GROUPS ? 0 : $customerGroups[$customerGroup];
    }

    /**
     * Retrieve product skus
     *
     * @return array
     */
    protected function retrieveOldSkus()
    {
        $oldSkus = $this->_connection->fetchPairs(
            $this->_connection->select()->from(
                $this->_connection->getTableName('catalog_product_entity'),
                ['sku', 'entity_id']
            )
        );
        return $oldSkus;
    }
}
