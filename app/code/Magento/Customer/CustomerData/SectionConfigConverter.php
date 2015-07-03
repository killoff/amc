<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

class SectionConfigConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Invalidate all sections marker
     */
    const INVALIDATE_ALL_SECTIONS_MARKER = '*';

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $sections = [];
        foreach ($source->getElementsByTagName('action') as $action) {
            $actionName = mb_strtolower($action->getAttribute('name'));
            foreach ($action->getElementsByTagName('section') as $section) {
                $sections[$actionName][] = mb_strtolower($section->getAttribute('name'));
            }
            if (!isset($sections[$actionName])) {
                $sections[$actionName] = self::INVALIDATE_ALL_SECTIONS_MARKER;
            }
        }
        return [
            'sections' => $sections,
        ];
    }
}
