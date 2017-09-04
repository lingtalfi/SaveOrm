<?php


namespace SaveOrm\Test;


use SaveOrm\Object\Object;
use SaveOrm\Test\GeneratedObjectManager;




class CoumeGeneratedBaseObject extends Object
{


    public function save()
    {
        $om = GeneratedObjectManager::create();
        $om->save($this);
        return $om->getSaveResults();
    }
}