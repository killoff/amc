<?php

namespace Amc\Timetable\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerStatus extends AbstractDb
{
    const STATUS_IDLE = 0;
    const STATUS_IN = 1;
    const STATUS_LATE = 2;
    const STATUS_CANCELLED = 3;

    protected function _construct()
    {
    }

    public function getAllStatuses()
    {
        return [
            self::STATUS_IDLE   => __('None'),
            self::STATUS_IN     => __('Arrived'),
            self::STATUS_LATE   => __('Late'),
            self::STATUS_IDLE   => __('Pending'),
        ];
    }

    public function updateStatus($customerId, $status)
    {
        $status = (string)$status;
        if (!array_key_exists($status, self::getAllStatuses())) {
            throw new \InvalidArgumentException("Status {$status} is not within allowed statuses to set");
        }
        $affected = $this->getConnection()->update(
            'customer_entity',
            ['timetable_status' => $status],
            ['entity_id = ?' => $customerId]
        );
        if (1 !== $affected) {
            throw new \Exception("Status {$status} was not updated for customer ID {$customerId}");
        }
    }
}
