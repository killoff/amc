<?php
namespace Amc\Consultation\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class Layout
{
    private $fieldDefaultAttributes = [
        'name' => '',
        'type' => '',
        'required' => 0,
        'label' => '',
    ];

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleDirReader;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $parser;

    public function __construct(
        \Magento\Framework\Module\Dir\Reader $dirReader,
        \Magento\Framework\Xml\Parser $xmlParser
    ) {
        $this->moduleDirReader = $dirReader;
        $this->parser = $xmlParser;
    }

    /**
     * todo: add caching
     *
     * @param string $layoutName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getLayoutConfig($layoutName)
    {
        $filePath = $this->moduleDirReader->getModuleDir('etc', 'Amc_Consultation') . '/layout.xml';
        $parsedArray = $this->parser->load($filePath)->xmlToArray();
        $result = [];
        foreach ($parsedArray['config']['_value']['layout'] as $layout) {
            $name = $layout['_attribute']['name'];
            $label = isset($layout['_attribute']['label']) ? $layout['_attribute']['label'] : '';
            $fields = $layout['_value']['field'];
            $result[$name] = ['name' => $name, 'label' => $label];
            foreach ($fields as $field) {
                $result[$name]['fields'][] = array_merge($this->fieldDefaultAttributes, $field['_attribute']);
            }
        }
        if (isset($result[$layoutName])) {
            return $result[$layoutName];
        } else {
            throw new NoSuchEntityException('Consultation layout not found for name ' . $layoutName);
        }
    }
}
