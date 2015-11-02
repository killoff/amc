<?php

namespace Amc\Clinic\Controller\Adminhtml\Room;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    /**
     * @var \Amc\Clinic\Model\RoomFactory
     */
    protected $roomFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerInterface;

    /**
     * @param Action\Context $context
     * @param \Amc\Clinic\Model\RoomFactory $roomFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $loggerInterface
     */
    public function __construct(
        Action\Context $context,
        \Amc\Clinic\Model\RoomFactory $roomFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        parent::__construct($context);
        $this->roomFactory = $roomFactory;
        $this->coreRegistry = $coreRegistry;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * Consultation save action
     */
    public function execute()
    {
        $roomId = $this->getRequest()->getParam('room_id');
        $returnToEdit = false;
        $editMode = false;

        try {
            $room = $this->roomFactory->create();

            if ($roomId) {
                $room->load($roomId);
                $editMode = true;
            }

            $room->addData($this->getRequest()->getParams());
            $room->save();

            $editMode
                ? $this->messageManager->addSuccess(__('You have updated the room.'))
                : $this->messageManager->addSuccess(__('You have created the room.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('An error occurred while saving the room.'));
            $returnToEdit = true;
        } catch (\Exception $e) {
            $this->loggerInterface->critical($e);
            $this->messageManager->addError($e->getMessage());
            $returnToEdit = true;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            $params = ['_current' => true];

            if ($roomId) {
                $params['room_id'] = $roomId;
            }

            $resultRedirect->setPath('clinic/room/edit', $params);
        } else {
            $resultRedirect->setPath('clinic/room', ['_current' => true]);
        }
        return $resultRedirect;
    }
}
