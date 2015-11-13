<?php

namespace Amc\Timetable\Ui\Component\Listing\Column\Customer;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class Options extends Column
{
    /**
     * @var string[]
     */
    protected $customers;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $collectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->initCustomerOptions($collectionFactory->create());
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function initCustomerOptions(\Magento\Customer\Model\ResourceModel\Customer\Collection $collection)
    {
        if (null === $this->customers) {
            foreach ($collection as $item) {
                $this->customers[$item->getId()] = $item->getName();
            }
        }
        return $this->customers;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = isset($this->customers[$item[$this->getData('name')]])
                    ? $this->customers[$item[$this->getData('name')]]
                    : $item[$this->getData('name')];
            }
        }

        return $dataSource;
    }
}
