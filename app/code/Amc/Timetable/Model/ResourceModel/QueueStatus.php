<?php

namespace Amc\Timetable\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QueueStatus extends AbstractDb
{
    const STATUS_IDLE      = 0;
    const STATUS_IN        = 1;
    const STATUS_LATE      = 2;
    const STATUS_PAID      = 3;
    const STATUS_CANCELLED = 4;

    protected function _construct()
    {
    }

    public function getAllStatuses()
    {
        return [
            self::STATUS_IDLE       => __('Pending'),
            self::STATUS_IN         => __('Arrived'),
            self::STATUS_LATE       => __('Late'),
            self::STATUS_PAID       => __('Paid'),
            self::STATUS_CANCELLED  => __('Cancelled'),
        ];
    }

    public function updateStatus($customerId, $context, $status, $changedBy)
    {
        $status = (int)$status;
        if (!array_key_exists($status, self::getAllStatuses())) {
            throw new \InvalidArgumentException("Status {$status} is not within allowed statuses to set");
        }
        $context = (int)$context;
        $this->getConnection()->delete(
            $this->getTable('amc_timetable_queue_status'),
            "customer_id={$customerId} AND context={$context}"
        );
        $this->getConnection()->insert(
            $this->getTable('amc_timetable_queue_status'),
            [
                'customer_id' => $customerId,
                'context' => $context,
                'status' => $status,
                'changed_by' => $changedBy,
                'changed_at' => (new \DateTime())->format('Y-m-d H:i:s')
            ]
        );
    }
}
