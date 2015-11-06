<?php

namespace Amc\Timetable\Ui\Component\Form\Field\User;

use Magento\Framework\Data\OptionSourceInterface;
use Amc\User\Model\ResourceModel\User\CollectionFactory;

class Options implements OptionSourceInterface
{
    /**
     * @var \Amc\User\Model\ResourceModel\User\Collection
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
        return $this->collection->toFormOptionArray(true);
    }
}
