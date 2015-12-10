<?php
namespace Amc\Protocol\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method \Amc\Protocol\Model\ResourceModel\Protocol _getResource()
 * @method \Amc\Protocol\Model\Protocol setName()
 */
class Protocol extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amc_protocol';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Protocol\Model\ResourceModel\Protocol');
    }

    public function saveProducts(array $productIds = [])
    {
        $this->_getResource()->saveProducts($this, $productIds);
    }

    public function saveHypertext($text)
    {
        $this->_getResource()->saveHypertext($this, $text);
    }

    public function saveHypertextRows($text)
    {
        $rows = $this->parseHypertext($text);
        $this->_getResource()->saveHypertextRows($this, $rows);
    }

    private function parseHypertext($text)
    {
        $result = [];
        $i = 1;
        $rows = explode("\n", $text);
        $isTemplate = false;
        foreach ($rows as $row) {
            $row = rtrim($row);
            // skip empty strings and comments
            if (trim($row) === '' || strpos(ltrim($row), ';') === 0) {
                continue;
            }
            $level = 0;
            if (preg_match('/^(\s+)/', $row, $matches)) {
                $level = strlen($matches[1]);
            }
            if (strpos($row, '///') !== false) {
                $isTemplate = false;
                $i++;
                continue;
            }
            if ($isTemplate) {
                if (strpos(ltrim($row), '_') === 0) {
                    $row = "\n" . ltrim($row, "_");
                }
                $result[$i]['text'] .= $row;
                continue;
            }
            // template
            if (strpos($row, '<@P>') !== false) {
                $row = str_replace('<@P>', '<>', $row);
                $isTemplate = true;
            }
            $result[$i]['level'] = $level;
            $result[$i]['title'] = $this->getRowTitle($row);
            $result[$i]['text'] = $this->getRowText($row);
            $result[$i]['action'] = $this->getRowAction($row);
            $result[$i]['parent'] = $this->getParentRowIndex($result, $i);
            if (!$isTemplate) {
                $i++;
            }
        }
//        print_r($result);
//        exit;
        return $result;
    }

    private function getRowText($text)
    {
        $textStart = strpos($text, '<');
        $textEnd = strpos($text, '>');
        if ($textStart !== false && $textEnd !== false && $textEnd > $textStart) {
            return substr($text, $textStart + 1, $textEnd - $textStart - 1);
        }
        return '';
    }

    private function getRowTitle($text)
    {
        $textStart = strpos($text, '<');
        $textEnd = strpos($text, '>');
        if ($textStart !== false && $textEnd !== false && $textEnd > $textStart) {
            return trim(substr($text, 0, $textStart));
        }
        return trim($text);
    }

    private function getRowAction($text)
    {
        if (preg_match('/\/([a-z0-9]+)$/i', $text, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function getParentRowIndex($allRows, $childIndex)
    {
        if (!isset($allRows[$childIndex - 1])) {
            return null;
        }
        for ($i = $childIndex - 1; $i >= 1; $i--) {
            if ($allRows[$i]['level'] < $allRows[$childIndex]['level']) {
                return $i;
            }
        }
        return null;
    }
}
