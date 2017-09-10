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
    protected $_identifierType = null;

    public static function create()
    {
        return new static();
    }

    /**
     * @param $identifierType string|null,
     *              - ai: auto-incremented field
     *              - pk: primary key
     *              - uq: first unique index found
     *              - ric: ric values
     *              - pr: object properties
     *
     * @return static
     */
    public static function createUpdate($identifierType = null)
    {
        $o = new static();
        $o->_mode = 'update';
        $o->_whereSuccess = null;
        $o->_where = null;
        $o->_identifierType = $identifierType;
        return $o;
    }


    public function _getManagerInfo()
    {
        return [
            'changedProperties' => $this->_changedProperties,
            'mode' => $this->_mode,
            'where' => $this->_where,
            'whereSuccess' => $this->_whereSuccess,
            'identifierType' => $this->_identifierType,
        ];
    }

}

