<?php


namespace SaveOrm\Object;

/**
 * This is the base object extended by all saveOrm objects.
 * So that if we want to add a method to all objects at once, we can.
 */
class Object
{
    protected $changedProperties = [];

    public static function create()
    {
        return new static();
    }


    public function _getChangedProperties()
    {
        return $this->changedProperties;
    }

}

