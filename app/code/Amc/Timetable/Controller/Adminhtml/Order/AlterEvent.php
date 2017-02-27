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
            $event = $this->jsonDecoder->decode(
                $this->getRequest()->getParam('event')
            );
            $this->validate($event);

            $saved = [];
            $eventData = $event;
            $uuid = isset($eventData['uuid']) ? $eventData['uuid'] : '';
//            if (! $uuid) {
//                continue;
//            }
            $eventData['room_id'] = 1; // todo: cannot define room_id so far
            $eventData['customer_id'] = $data['customer_id'];
            /** @var \Amc\Timetable\Model\OrderEvent $eventModel */
            $eventModel = $this->orderEventFactory->create();
            $eventModel->load($uuid,'uuid');
            if (isset($eventData['deleted']) && $eventData['deleted']) {
                $eventModel->delete();
            } else {
                $eventModel->addData($eventData);
                $eventModel->save();
            }
            $saved[$uuid] = $eventModel->getId();
            $response['saved'] = $saved;
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
//        if (!isset($data['customer_id']) || !isset($data['events'])) {
//            throw new \DomainException('Customer ID and events collection must exist.');
//        }
//        if (!is_array($data['events'])) {
//            throw new \DomainException('Events must be type of array.');
//        }
    }
}
