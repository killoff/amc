<?php
namespace Amc\Protocol\Model;


class TestClass implements TestInterface
{
    public function testMe()
    {
        return __CLASS__;
    }
}
