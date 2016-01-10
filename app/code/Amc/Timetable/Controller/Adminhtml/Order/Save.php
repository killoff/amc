<?php

namespace Amc\Timetable\Controller\Adminhtml\Order;

class Save extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\DataObject\Factory */
    private $orderEventFactory;

    /** @var \Magento\Framework\Json\Encoder */
    private $jsonEncoder;

    /** @var \Magento\Framework\Json\Decoder */
    private $jsonDecoder;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amc\Timetable\Model\OrderEventFactory $orderEventFactory,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder

    ) {
        parent::__construct($context);
        $this->orderEventFactory = $orderEventFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }


    public function execute()
    {
        try {
            $response = array('error' => false);
            $data = $this->jsonDecoder->decode(
                $this->getRequest()->getParam('data')
            );
            $this->validate($data);

            // nothing changed in timetable
            if (count($data['events']) == 0) {
                $this->getResponse()->setContent($this->jsonEncoder->encode($response));
                return;
            }
            $saved = [];
            foreach ($data['events'] as $uuid => $eventData) {
                $eventData['customer_id'] = $data['customer_id'];
                /** @var \Amc\Timetable\Model\OrderEvent $eventModel */
                $eventModel = $this->orderEventFactory->create();
                $eventModel->load($uuid,'uuid');
                $eventModel->setData($eventData);
                $eventModel->save();
                $saved[$eventModel->getId()] = $uuid;
            }
            $response['saved'] = $saved;

            $this->getResponse()->setContent($this->jsonEncoder->encode($response));

        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
            $this->getResponse()->setContent($this->jsonEncoder->encode($response));
        }
    }

    private function validate($data)
    {
        if (!is_array($data)) {
            throw new \DomainException('Timetable data must be json encoded.');
        }
        if (!isset($data['customer_id']) || !isset($data['events'])) {
            throw new \DomainException('Customer ID and events collection must exist.');
        }
        if (!is_array($data['events'])) {
            throw new \DomainException('Events must be type of array.');
        }
    }
}
