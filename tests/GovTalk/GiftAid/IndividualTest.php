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

use GovTalk\GiftAid\TestCase;

/**
 * The base class for all GovTalk\GiftAid\Individual tests
 */
class IndividualTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->individual = new Individual(
            'Mr',
            'Rex',
            'Muck',
            '077 1234 5678',
            '3',
            'SW1A 1AA'
        );
    }
    
    public function testIndividualCreation()
    {
        $this->assertEquals($this->individual->getTitle(), 'Mr');
        $this->assertEquals($this->individual->getSurname(), 'Muck');
        $this->assertEquals($this->individual->getForename(), 'Rex');
        $this->assertEquals($this->individual->getPhone(), '077 1234 5678');
        $this->assertEquals($this->individual->getHouseNum(), '3');
        $this->assertEquals($this->individual->getPostcode(), 'SW1A 1AA');
    }
    
    public function testUpdateIndividual()
    {
        $this->individual->setTitle('Mrs');
        $this->individual->setSurname('Malady');
        $this->individual->setForename('Regina');
        $this->individual->setPhone('020 8765 4321');
        $this->individual->setHouseNum('2');
        $this->individual->setPostcode('NW1A 1AA');

        $this->assertEquals($this->individual->getTitle(), 'Mrs');
        $this->assertEquals($this->individual->getSurname(), 'Malady');
        $this->assertEquals($this->individual->getForename(), 'Regina');
        $this->assertEquals($this->individual->getPhone(), '020 8765 4321');
        $this->assertEquals($this->individual->getHouseNum(), '2');
        $this->assertEquals($this->individual->getPostcode(), 'NW1A 1AA');
    }
}
