<?php
namespace Amc\Translate\Block\Adminhtml\Index;

use Magento\Backend\Block\Widget\Container;
use Amc\Translate\Model\Helper;

class Textarea extends Container
{
    /** @var \Amc\Translate\Model\Helper */
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        Helper $helper,
        array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->add(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save',
                'onclick' => 'jQuery("#translation_form").submit()'
            ]
        );
    }

    /**
     * @return array
     */
    public function getTranslationsAsText()
    {
        $defaultLocale = $this->helper->getDefaultLocale();
        $translations = $this->helper->getTranslations($defaultLocale);
        ksort($translations);
        $text = '';
        foreach ($translations as $string => $translated) {
            $text .= sprintf("%s ".Helper::SEPARATOR." %s\n", $string, $translated);
        }
        return $text;
    }
}
