<?php
namespace Amc\Timetable\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Queue extends Template
{
    public function getComponentOptionsJson()
    {
        $options = [
            'component' => 'Amc_Timetable/js/queue',
            'template' => 'Amc_Timetable/queue',
            'source_url' => $this->_urlBuilder->getUrl('timetable/queue/jsonFeed', ['date' => $this->getTodayDate()->format('Y-m-d')]),
            'invoice_url' => $this->_urlBuilder->getUrl('timetable/queue/invoice'),
            'pay_url' => $this->_urlBuilder->getUrl('timetable/queue/invoice'),
            'change_status_url' => $this->_urlBuilder->getUrl('timetable/queue/changeStatus'),
            'edit_customer_url_prefix' => $this->_urlBuilder->getUrl('customer/index/edit') . 'id/',
        ];

        return \Zend_Json::encode($options);
    }

    /**
     * @return \DateTime
     */
    public function getTodayDate()
    {
        return (new \DateTime())->setTime(0, 0, 0);
    }

    public function getTodayDateFormat()
    {
        return $this->getTodayDate()->format('D, j.m');
    }
}
