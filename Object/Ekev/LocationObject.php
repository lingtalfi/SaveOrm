<?php


namespace SaveOrm\Object\Ekev;


use SaveOrm\Object\Object;

class LocationObject extends Object
{

    private $id;
    private $label;
    private $address;
    private $city;
    private $postcode;
    private $phone;
    private $extra;
    private $country_id;
    private $shop_id;


    public function __construct()
    {
        $this->id = null;
        $this->label = "";
        $this->address = "";
        $this->city = "";
        $this->postcode = "";
        $this->phone = "";
        $this->extra = "";
        $this->country_id = null;
        $this->shop_id = null;
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
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return null
     */
    public function getCountryId()
    {
        return $this->country_id;
    }

    public function setCountryId($country_id)
    {
        $this->country_id = $country_id;
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


}