<?php
namespace Amc\Consultation\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Amc\Consultation\Model\Consultation\Builder as ConsultationBuilder;
use Amc\Consultation\Model\Layout as ConsultationLayout;
class Open extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ConsultationBuilder
     */
    protected $consultationBuilder;

    /**
     * @var ConsultationLayout
     */
    protected $consultationLayout;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ConsultationBuilder $consultationBuilder,
        ConsultationLayout $consultationLayout
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->consultationBuilder = $consultationBuilder;
        $this->consultationLayout = $consultationLayout;
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $consultationId = $this->getRequest()->getParam('consultation_id');

        $consultation = $this->consultationBuilder->loadConsultation($consultationId);

        /** @var \Magento\Backend\Block\Template $content */
        $content = $this->layoutFactory->create()->createBlock(
            'Magento\Backend\Block\Template',
            '',
            [
                'data' => [
                    'values' => json_decode($consultation->getJsonData(), true),
                    'layout' => $this->getLayoutConfig($consultation->getProduct())
                ]
            ]
        );
        $content->setTemplate('Amc_Consultation::consultation/view.phtml');

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($content->toHtml());
    }

    private function getLayoutConfig($product)
    {
        $layoutName = $product->getData('consultation_layout');
        try {
            return $this->consultationLayout->getLayoutConfig($layoutName);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

}
