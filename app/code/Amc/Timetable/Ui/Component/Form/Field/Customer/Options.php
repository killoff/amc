<?php

namespace Amc\Timetable\Ui\Component\Form\Field\Customer;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class Options implements OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $collection;

    /**
     * Options constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['label' => __('Please Select'), 'value' => '']];
        foreach ($this->collection as $item) {
            $options[] = [
                'label' => $item->getName(),
                'value' => $item->getId()
            ];
        }
        return $options;
    }
}
