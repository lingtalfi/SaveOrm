<?php


namespace SaveOrm\Object\Ekev;


use SaveOrm\Object\Object;

class CourseObject extends Object
{

    private $id;
    private $shop_id;
    private $name;


    public function __construct()
    {
        $this->id = null;
        $this->shop_id = null;
        $this->name = "";
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null
     */
    public function getShopId()
    {
        return $this->shop_id;
    }

    public function setShopId($shop_id)
    {
        $this->shop_id = $shop_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

}