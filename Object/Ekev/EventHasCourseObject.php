<?php


namespace SaveOrm\Object\Ekev;


use SaveOrm\Object\Object;

class EventHasCourseObject extends Object
{

    private $id;
    private $event_id;
    private $course_id;
    private $date;
    private $start_time;
    private $end_time;
    private $presenter_group_id;
    private $capacity;


    public function __construct()
    {
        $this->id = null;
        $this->event_id = null;
        $this->course_id = null;
        $this->date = "0000-00-00";
        $this->start_time = "";
        $this->end_time = "";
        $this->presenter_group_id = null;
        $this->capacity = 0;
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
    public function getEventId()
    {
        return $this->event_id;
    }

    public function setEventId($event_id)
    {
        $this->event_id = $event_id;
        return $this;
    }

    /**
     * @return null
     */
    public function getCourseId()
    {
        return $this->course_id;
    }

    public function setCourseId($course_id)
    {
        $this->course_id = $course_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
        return $this;
    }

    /**
     * @return null
     */
    public function getPresenterGroupId()
    {
        return $this->presenter_group_id;
    }

    public function setPresenterGroupId($presenter_group_id)
    {
        $this->presenter_group_id = $presenter_group_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
        return $this;
    }


}