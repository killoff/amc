<?php

namespace Amc\Timetable\Model\Adapter;

/**
 *
 * Class Aggregated
 * @package Amc\Timetable\Model
 */

class Fullcalendar
{
    /**
     * Based on results of \Amc\Timetable\Model\Aggregated prepare Scheduler Resources array
     *
     * @param array $aggregated
     * @return array
     */
    public function getSchedulerResources(array $aggregated)
    {
        $resources = [];
        foreach ($aggregated['products'] as $product) {
            $resource = [
                'id' => sprintf('i%s', $product['sales_item_id']),
                'product_id' => $product['id'],
                'sales_item_id' => $product['sales_item_id'],
                'title' => $product['name'],
                'type' => 'item',
                'duration' => empty($product['duration']) ? 15 : $product['duration'],
                'children' => []
            ];
            $productUsers = array_filter($aggregated['users'], function ($user) use ($product) {
                return in_array($product['id'], $user['product_ids']);
            });
            foreach ($productUsers as $user) {
                $resource['children'][] = [
                    'id' => sprintf('i%s_u%s', $product['sales_item_id'], $user['id']),
                    'product_id' => $product['id'],
                    'sales_item_id' => $product['sales_item_id'],
                    'user_id' => $user['id'],
                    'title' => $user['name'],
                    'type' => 'user',
                ];
            }
            $resources[] = $resource;
        }
        return $resources;
    }

    /**
     * Based on results of \Amc\Timetable\Model\Aggregated prepare Scheduler Events array
     *
     * @param array $aggregated
     * @return array
     */
    public function getSchedulerEvents(array $aggregated)
    {
        $events = [];
        $schedulerResources = $this->getSchedulerResources($aggregated);
        foreach ($schedulerResources as $productResource) {
            foreach ($productResource['children'] as $userResource) {
                // todo: rename?
                $userSchedule = array_filter($aggregated['events'], function ($event) use ($userResource) {
                    return $event['user_id'] == $userResource['user_id'];
                });

                foreach ($userSchedule as $event) {
                    $events[] = [
                        'resourceId' => $userResource['id'],
//                        'id'         => $event['id'], // todo
                        'start'      => $event['start_at'],
                        'end'        => $event['end_at'],
                        'rendering'  => $event['order_item_id'] === $userResource['sales_item_id'] ? '' : 'background',
                        // todo: make it human-readable
                        'color'      => $event['type'] === 'schedule' ? '#00c853' : ($event['order_item_id'] === $userResource['sales_item_id'] ? '#5300c8' : '#ff8a80'),
                        'overlap'    => true,
                        'title'      => '', // 'room '.$occupied->getRoomId(),
                        'type'       => $event['type'],
                        'room_id'    => $event['room_id'],
                        'user_id'    => $event['user_id'],
                        'sales_item_id' => $userResource['sales_item_id'],
                    ];
                }
            }
        }

        return $events;
    }

}

