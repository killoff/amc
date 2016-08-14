<?php

namespace Amc\Clinic\Controller\Adminhtml\Room;

use Magento\Backend\App\Action;

class Edit extends Action
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
     * @param Action\Context $context
     * @param \Amc\Clinic\Model\RoomFactory $roomFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        \Amc\Clinic\Model\RoomFactory $roomFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->roomFactory = $roomFactory;
        $this->coreRegistry = $coreRegistry;
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Amc_Clinic::clinic_room'
        )->_addBreadcrumb(
            __('Clinic'),
            __('Clinic')
        )->_addBreadcrumb(
            __('Room'),
            __('Room')
        );
        return $this;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $roomId = $this->getRequest()->getParam('room_id');
        /** @var \Magento\User\Model\User $model */
        $model = $this->roomFactory->create();

        if ($roomId) {
            $model->load($roomId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This room no longer exists.'));
                $this->_redirect('clinic/room/');
                return;
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getRoomData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('current_room', $model);

        if (isset($roomId)) {
            $breadcrumb = __('Edit Room');
        } else {
            $breadcrumb = __('New Room');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Rooms'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Room'));
        $this->_view->renderLayout();
    }
}
