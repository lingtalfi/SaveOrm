<?php


namespace SaveOrm\Object\Ekev;


use SaveOrm\Object\Object;

class EventLangObject extends Object
{

    private $event_id;
    private $lang_id;
    private $label;


    public function __construct()
    {
        $this->event_id = null;
        $this->lang_id = null;
        $this->label = "";
    }


    //--------------------------------------------
    //
    //--------------------------------------------
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
    public function getLangId()
    {
        return $this->lang_id;
    }

    public function setLangId($lang_id)
    {
        $this->lang_id = $lang_id;
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

}