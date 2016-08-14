<?php

namespace Amc\User\Model\ResourceModel\User;

class Collection extends \Magento\User\Model\ResourceModel\User\Collection
{
    /**
     * @return array
     */
    public function toColumnOptionArray()
    {
        $res = [];
        foreach ($this as $item) {
            $res[$item->getId()] = $item->getName();
        }
        return $res;
    }

    /**
     * @return array
     */
    public function toFormOptionArray($withEmpty = false)
    {
        $res = $withEmpty ? [['value' => '', 'label' => __('Please Select')]] : [];
        foreach ($this as $item) {
            $res[] = [
                'value' => $item->getId(),
                'label' => $item->getName()
            ];
        }
        return $res;
    }
}
