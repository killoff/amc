<?php

namespace Amc\Timetable\Ui\Component\Listing\Column\User;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Amc\User\Model\ResourceModel\User\CollectionFactory;

class Options extends Column
{
    /**
     * @var string[]
     */
    protected $users;

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
        $this->users = $collectionFactory->create()->toColumnOptionArray();
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                $item[$this->getData('name')] = isset($this->users[$item[$this->getData('name')]])
                    ? $this->users[$item[$this->getData('name')]]
                    : $item[$this->getData('name')];
            }
        }

        return $dataSource;
    }
}
