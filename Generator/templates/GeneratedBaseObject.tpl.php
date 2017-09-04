<?php


namespace SaveOrm\Test;


use SaveOrm\Object\Object;
use SaveOrm\Test\GeneratedObjectManager;


class CoumeGeneratedBaseObject extends Object
{

    private $_savedResults;

    public function save()
    {
        $om = GeneratedObjectManager::create();
        $ret = $om->save($this);
        $this->_savedResults = $om->getSaveResults();
        return $ret;
    }


    public function getSaveResults()
    {
        return $this->_savedResults;
    }
}