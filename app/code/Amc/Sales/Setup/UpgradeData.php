<?php

namespace Amc\Sales\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $attributeRepository;

    /**
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     */
    public function __construct(\Magento\Eav\Model\AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $attributesThatShouldBeRequired = ['dob', 'gender'];
        foreach ($attributesThatShouldBeRequired as $attributeCode) {
            $attribute = $this->attributeRepository->get('customer', $attributeCode);
            $attribute->setIsRequired(true);
            $this->attributeRepository->save($attribute);
        }
        $setup->endSetup();
    }
}
