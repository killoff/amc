<?php

namespace Amc\Timetable\Ui\Component\Form\Field\Room;

use Magento\Framework\Data\OptionSourceInterface;
use Amc\Clinic\Model\ResourceModel\Room\CollectionFactory;

class Options implements OptionSourceInterface
{
    /**
     * @var \Amc\Clinic\Model\ResourceModel\Room\Collection
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
