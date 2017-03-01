<?php

namespace Amc\Timetable\Controller\Adminhtml\Order;

class AlterEvent extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\DataObject\Factory */
    private $orderEventFactory;

    /** @var \Magento\Framework\Json\Decoder */
    private $jsonDecoder;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amc\Timetable\Model\OrderEventFactory $orderEventFactory,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->orderEventFactory = $orderEventFactory;
        $this->jsonDecoder = $jsonDecoder;
        $this->resultJsonFactory = $resultJsonFactory;
    }


    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $response = array('error' => false, 'saved' => []);
            $eventData = $this->jsonDecoder->decode(
                $this->getRequest()->getParam('event')
            );
            $this->validate($eventData);
            $uuid = $eventData['uuid'];
            /** @var \Amc\Timetable\Model\OrderEvent $event */
            $event = $this->orderEventFactory->create();
            $event->load($uuid,'uuid');
            if ($event->getId()) {
                if (isset($eventData['deleted']) && $eventData['deleted']) {
                    $event->delete();
                } else {
                    $event->addData([
                        'start_at' => $eventData['start_at'],
                        'end_at' => $eventData['end_at'],
                    ]);
                    $event->save();
                }
            } else {

            }
            $response['event'] = [];
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        }
        return $resultJson->setData($response);
    }

    private function validate($data)
    {
        if (!is_array($data)) {
            throw new \DomainException('Timetable data must be json encoded.');
        }
        if (!isset($data['uuid']) || empty($data['uuid'])) {
            throw new \DomainException('Event uuid is missing or empty.');
        }
    }
}
