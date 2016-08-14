<?php

namespace Amc\Translate\Controller\Adminhtml\Index;

use Amc\Translate\Model\Helper;
use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * @var \Magento\Translation\Model\ResourceModel\StringFactory
     */
    protected $resourceFactory;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $appCache;

    /** @var \Amc\Translate\Model\Helper */
    protected $helper;

    public function __construct(
        Action\Context $context,
        \Magento\Translation\Model\ResourceModel\StringUtilsFactory $resource,
        \Magento\Framework\App\Cache\TypeListInterface $appCache,
        Helper $helper
    ) {
        $this->resourceFactory = $resource;
        $this->appCache = $appCache;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Consultation save action
     */
    public function execute()
    {
        try {
            $text = $this->_request->getParam('translations');
            $lines = explode("\n", $text);
            $translations = [];
            // parse text
            foreach ($lines as $line) {
                $line = trim($line);
                if (false === strpos($line, Helper::SEPARATOR)) {
                    continue;
                }
                list ($string, $translate) = explode(Helper::SEPARATOR, $line);
                $string = trim($string);
                if (empty($string)) {
                    continue;
                }
                $translations[$string] = trim($translate);
            }

            $defaultLocale = $this->helper->getDefaultLocale();

            // define what's need to be updated
            $existing = $this->helper->getTranslations($defaultLocale);
            $changed = array_diff_assoc($translations, $existing);

            if (count($changed) > 0) {

                $this->appCache->invalidate(\Magento\Framework\App\Cache\Type\Translate::TYPE_IDENTIFIER);

                /** @var $resource \Magento\Translation\Model\ResourceModel\StringUtils */
                $resource = $this->resourceFactory->create();

                $storeId = 0;
                foreach ($changed as $string => $translate) {
                    $resource->saveTranslate($string, $translate, $defaultLocale, $storeId);
                }
            }
            $this->messageManager->addSuccess(__('You saved translations.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while saving the translations.'.$e->getMessage()));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('translate/index/index');
        return $resultRedirect;
    }
}
