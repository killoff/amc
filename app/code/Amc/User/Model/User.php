<?php

namespace Amc\User\Model;

class User extends \Magento\User\Model\User
{
    public function getAssignedProducts()
    {
        return [];
    }
}
