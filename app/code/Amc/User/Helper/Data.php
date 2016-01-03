<?php

namespace Amc\User\Helper;

class Data
{
    public function getUserShortName($user)
    {
        return $this->shortenUserName($user->getData('firstname'), $user->getData('lastname'), $user->getData('user_fathername'));
    }

    public function shortenUserName($firstName, $lastName, $fatherName)
    {
        return $lastName
            . ' ' . mb_substr($firstName, 0, 1)
            . '.' . (empty($fatherName) ? '' : ' '.mb_substr($fatherName, 0, 1) . '.');
    }


}
