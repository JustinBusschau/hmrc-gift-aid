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

use GovTalk\GiftAid\Individual;

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
