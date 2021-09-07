<?php

namespace GovTalk\GiftAid;

class Individual
{
    private $title      = '';
    private $surname    = '';
    private $forename   = '';
    private $phone      = '';
    private $houseNum   = '';
    private $postcode   = '';
    private $isOverseas = false;

    public function __construct($title, $name, $surname, $phone, $houseNum, $postcode, $overseas = false)
    {
        $this->title      = $title;
        $this->surname    = $surname;
        $this->forename   = $name;
        $this->phone      = $phone;
        $this->houseNum   = $houseNum;
        $this->postcode   = $postcode;
        $this->isOverseas = $overseas;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($value)
    {
        $this->surname = $value;
    }

    public function getForename()
    {
        return $this->forename;
    }

    public function setForename($value)
    {
        $this->forename = $value;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;
    }

    public function getHouseNum()
    {
        return $this->houseNum;
    }

    public function setHouseNum($value)
    {
        $this->houseNum = substr($value, 0, 40);
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function setPostcode($value)
    {
        $this->postcode = $value;
    }

    public function getIsOverseas()
    {
        return ($this->isOverseas === true) ? 'yes' : 'no';
    }

    public function setIsOverseas($value)
    {
        $this->isOverseas = $value;
    }
}
