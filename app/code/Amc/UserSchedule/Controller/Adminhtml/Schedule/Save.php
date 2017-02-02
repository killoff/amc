<?php

namespace Amc\UserSchedule\Controller\Adminhtml\Schedule;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\DecoderInterface;

class Save extends Action
{
    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Amc\UserSchedule\Model\Schedule\ItemFactory
     */
    protected $scheduleItemFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param Action\Context $context
     * @param DecoderInterface $jsonDecoder
     * @param \Amc\UserSchedule\Model\Schedule\ItemFactory $scheduleItemFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        Action\Context $context,
        DecoderInterface $jsonDecoder,
        \Amc\UserSchedule\Model\Schedule\ItemFactory $scheduleItemFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->jsonDecoder = $jsonDecoder;
        $this->scheduleItemFactory = $scheduleItemFactory;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        if ($this->getRequest()->getParam('data')) {
            $data = $this->jsonDecoder->decode(
                $this->getRequest()->getParam('data')
            );

            foreach ($data as $scheduleItem) {
                $model = $this->scheduleItemFactory->create();
                if (!empty($scheduleItem['entity_id'])) {
                    $model->load($scheduleItem['entity_id']);
                }
                if (isset($scheduleItem['deleted']) && $scheduleItem['deleted']) {
                    if ($model->getId()) {
                        $model->delete();
                    }
                    // we want to continue if event deleted, that's why not ($scheduleItem['deleted'] && $model->getId())
                    continue;
                }
                $model->setData($scheduleItem);
                $startAt = $this->timezone->date($scheduleItem['start_at'])->setTimezone(new \DateTimeZone('UTC'));
                $endAt = $this->timezone->date($scheduleItem['end_at'])->setTimezone(new \DateTimeZone('UTC'));
                $model->setStartAt($startAt);
                $model->setEndAt($endAt);
                $model->save();
            }
        }
    }
}
