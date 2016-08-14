<?php

namespace Amc\Translate\Model;

class Helper
{
    const SEPARATOR = '=>';

    /** @var \Magento\Translation\Model\ResourceModel\Translate */
    protected $translate;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    public function __construct(
        \Magento\Translation\Model\ResourceModel\Translate $translate,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->translate = $translate;
        $this->scopeConfig = $scopeConfig;
    }

    public function getTranslations($locale)
    {
        return $this->translate->getTranslationArray(null, $locale);
    }

    public function getDefaultLocale()
    {
        return $this->scopeConfig->getValue('translate/default_locale');
    }
}
