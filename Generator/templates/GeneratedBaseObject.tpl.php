<?php


namespace SaveOrm\Test;


use SaveOrm\Object\Object;
use SaveOrm\Test\GeneratedObjectManager;




class CoumeGeneratedBaseObject extends Object
{


    public function save()
    {
        GeneratedObjectManager::create()->save($this);
    }
}