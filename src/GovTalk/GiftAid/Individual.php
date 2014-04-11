<?php

/*
 * This file is part of the GovTalk\GiftAid package
 *
 * (c) Justin Busschau
 *
 * For the full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 */

namespace GovTalk\GiftAid;

class Individual
{
    private $title = '';
    private $surname = '';
    private $forename = '';
    private $phone = '';
    private $houseNum = '';
    private $postcode = '';

    public function __construct($title, $name, $surname, $phone, $houseNum, $postcode)
    {
        $this->title = $title;
        $this->surname = $surname;
        $this->forename = $name;
        $this->phone = $phone;
        $this->houseNum = $houseNum;
        $this->postcode = $postcode;
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
}
