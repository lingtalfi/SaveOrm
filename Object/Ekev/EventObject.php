<?php


namespace SaveOrm\Object\Ekev;


use SaveOrm\Object\Object;

class EventObject extends Object
{

    private $id;
    private $shop_id;
    private $name;
    private $start_date;
    private $end_date;
    private $location_id;

    private $eventLang;
    private $location;
    private $courses;


    public function __construct()
    {
        $this->id = null;
        $this->shop_id = null;
        $this->name = "";
        $this->start_date = "0000-00-00";
        $this->end_date = "0000-00-00";
        $this->location_id = null;
        $this->eventLang = null;
        $this->courses = [];

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

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
        return $this;
    }

    /**
     * @return null
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    public function setLocationId($location_id)
    {
        $this->location_id = $location_id;
        return $this;
    }

    /**
     * @return null|EventLangObject
     */
    public function getEventLang()
    {
        return $this->eventLang;
    }

    public function createEventLang(EventLangObject $eventLang)
    {
        $this->eventLang = $eventLang;
        return $this;
    }

    /**
     * @return null|LocationObject
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation(LocationObject $location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return CourseObject[]
     */
    public function getCourses()
    {
        return $this->courses;
    }

    public function addCourse(CourseObject $course, EventHasCourseObject $hasObj)
    {
        $this->courses[] = $course;
        $course->_has_ = $hasObj;
        return $this;
    }

}