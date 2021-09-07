<?php

namespace GovTalk\GiftAid;

/**
 * The base class for all GovTalk\ClaimingOrganisation tests
 */
class ClaimingOrganisationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->claimant = new ClaimingOrganisation(
            'A Charitible Crowd',
            'AB12345',
            'CCEW',
            '2584789658'
        );
    }

    public function testOrganisationCreation()
    {
        $this->assertEquals($this->claimant->getName(), 'A Charitible Crowd');
        $this->assertEquals($this->claimant->getHmrcRef(), 'AB12345');
        $this->assertEquals($this->claimant->getRegulator(), 'CCEW');
        $this->assertEquals($this->claimant->getRegNo(), '2584789658');
    }

    public function testOrganisationChange()
    {
        $this->claimant->setName('Another Fine Bunch');
        $this->claimant->setHmrcRef('CD67890');
        $this->claimant->setRegulator('OSCR');
        $this->claimant->setRegNo('3695897469');

        $this->assertEquals($this->claimant->getName(), 'Another Fine Bunch');
        $this->assertEquals($this->claimant->getHmrcRef(), 'CD67890');
        $this->assertEquals($this->claimant->getRegulator(), 'OSCR');
        $this->assertEquals($this->claimant->getRegNo(), '3695897469');
    }

    public function testConnectedCharities()
    {
        $this->claimant->setHasConnectedCharities(true);
        $this->assertTrue($this->claimant->getHasConnectedCharities());

        // non-bool values are treated as false
        $this->claimant->setHasConnectedCharities('0');
        $this->assertFalse($this->claimant->getHasConnectedCharities());

        $org = new ClaimingOrganisation(
            'Giving Is Good',
            'EF24680',
            'CCEW',
            '8526321452'
        );

        $this->claimant->addConnectedCharity($org);

        $org_a = $this->claimant->getConnectedCharities();
        $this->assertEquals(count($org_a), 1);

        $org->setName('Greater Give');
        $org->setHmrcRef('GH13579');
        $org->setRegulator('OSCR');
        $org->setRegNo('6542147854');
        $this->claimant->addConnectedCharity($org);

        $org_a = $this->claimant->getConnectedCharities();
        $this->assertEquals(count($org_a), 2);

        $this->claimant->clearConnectedCharities();
        $org_a = $this->claimant->getConnectedCharities();
        $this->assertEquals(count($org_a), 0);
    }

    public function testCommunityBuildings()
    {
        $this->claimant->setUseCommunityBuildings(false);
        $this->assertFalse($this->claimant->getUseCommunityBuildings());

        // non-bool values are treated as false
        $this->claimant->setUseCommunityBuildings('1');
        $this->assertFalse($this->claimant->getUseCommunityBuildings());
    }
}
