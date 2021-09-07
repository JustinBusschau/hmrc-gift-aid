<?php

namespace GovTalk\GiftAid;

class AuthorisedOfficial extends Individual
{
    public function __construct($title, $name, $surname, $phone, $postcode)
    {
        parent::__construct($title, $name, $surname, $phone, null, $postcode);
    }

    public function getHouseNum()
    {
        return null;
    }

    public function setHouseNum($value)
    {
    }
}
