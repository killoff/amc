<?php

namespace Amc\Clinic\Controller\Adminhtml\Room;

use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var \Amc\Clinic\Model\RoomFactory
     */
    protected $roomFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerInterface;

    /**
     * @param Action\Context $context
     * @param \Amc\Clinic\Model\RoomFactory $roomFactory
     * @param \Psr\Log\LoggerInterface $loggerInterface
     */
    public function __construct(
        Action\Context $context,
        \Amc\Clinic\Model\RoomFactory $roomFactory,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        parent::__construct($context);
        $this->roomFactory = $roomFactory;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($roomId = $this->getRequest()->getParam('room_id')) {
            try {
                $model = $this->roomFactory->create();
                $model->setId($roomId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the room.'));
                $this->_redirect('clinic/room/');
                return;
            } catch (\Exception $e) {
                $this->loggerInterface->critical($e);
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('clinic/room/edit', ['room_id' => $roomId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a room to delete.'));
        $this->_redirect('clinic/room/');
    }
}
