<?php


namespace SaveOrm\Object;

/**
 * This is the base object extended by all saveOrm objects.
 * So that if we want to add a method to all objects at once, we can.
 */
class Object
{
    protected $_changedProperties = [];
    protected $_mode = 'insert';
    protected $_where = [];
    protected $_whereSuccess = false;

    public static function create()
    {
        return new static();
    }


    public function _getManagerInfo()
    {
        return [
            'changedProperties' => $this->_changedProperties,
            'mode' => $this->_mode,
            'where' => $this->_where,
            'whereSuccess' => $this->_whereSuccess,
        ];
    }

}

