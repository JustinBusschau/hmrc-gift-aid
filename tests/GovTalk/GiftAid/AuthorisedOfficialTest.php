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

/**
 * The base class for all GovTalk\AuthorisedOfficial tests
 */
class AuthorisedOfficialTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->officer = new AuthorisedOfficial(
            'Mr',
            'Rex',
            'Muck',
            '077 1234 5678',
            'SW1A 1AA'
        );
    }

    public function testAuthorisedOfficialCreation()
    {
        $this->assertEquals($this->officer->getTitle(), 'Mr');
        $this->assertEquals($this->officer->getSurname(), 'Muck');
        $this->assertEquals($this->officer->getForename(), 'Rex');
        $this->assertEquals($this->officer->getPhone(), '077 1234 5678');
        $this->assertEquals($this->officer->getPostcode(), 'SW1A 1AA');
    }

    public function testUpdateAuthorisedOfficial()
    {
        $this->officer->setTitle('Mrs');
        $this->officer->setSurname('Malady');
        $this->officer->setForename('Regina');
        $this->officer->setPhone('020 8765 4321');
        $this->officer->setPostcode('NW1A 1AA');

        $this->assertEquals($this->officer->getTitle(), 'Mrs');
        $this->assertEquals($this->officer->getSurname(), 'Malady');
        $this->assertEquals($this->officer->getForename(), 'Regina');
        $this->assertEquals($this->officer->getPhone(), '020 8765 4321');
        $this->assertEquals($this->officer->getPostcode(), 'NW1A 1AA');
    }

    public function testHouseNumOmission()
    {
        $this->assertNull($this->officer->getHouseNum());

        $this->officer->setHouseNum('any');

        $this->assertNull($this->officer->getHouseNum());
    }
}
