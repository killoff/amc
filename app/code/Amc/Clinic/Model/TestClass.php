<?php
namespace Amc\Clinic\Model;


class TestClass implements \Amc\Protocol\Model\TestInterface
{
    public function testMe()
    {
        return __CLASS__;
    }
}
