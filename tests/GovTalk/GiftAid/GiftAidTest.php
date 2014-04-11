<?php

/*
 * This file is part of the GovTalk\GiftAid package
 *
 * (c) By The People Ltd.
 *
 * For the full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 */

namespace GovTalk\GiftAid;

use GovTalk\GiftAid\TestCase;

/**
 * The base class for all GovTalk\GiftAid tests
 */
class GiftAidTest extends TestCase
{
    /**
     * The gateway user ID
     */
    private $gatewayUserID;

    /**
     * The gateway user password
     */
    private $gatewayUserPassword;

    /**
     * The gateway vendor ID
     */
    private $gatewayVendorID;

    /**
     * The product submitting the claim
     */
    private $gatewaySoftware;

    /**
     * The product version
     */
    private $gatewaySoftVersion;

    public function setUp()
    {
        parent::setUp();

        /**
         * The user name (Sender ID) and password given below are not valid for
         * either the live or any of the test/dev gateways. If you want to run
         * this test suite against actual servers, please contact the relevant
         * agency (HMRC / Companies House / etc.) and apply for valid credentials.
         */
        $this->gatewayUserID = 'XMLGatewayTestUserID';
        $this->gatewayUserPassword = 'XMLGatewayTestPassword';
        $this->gatewayVendorID = 'GatewaySubmitter';
        $this->gatewaySoftware = 'GivingSoft';
        $this->gatewaySoftVersion = '1.2.0';

        /**
         * An authorised official for testing ...
         */
        $this->officer = new AuthorisedOfficial(
            null,
            'Bob',
            'Smith',
            '01234 567890',
            'AB12 3CD'
        );

        /**
         * A claiming organisation
         */
        $this->claimant = new ClaimingOrganisation(
            'A Fundraising Organisation',
            'AB12345',
            'CCEW',
            '123456'
        );

        /**
         * A test claim
         */
        $this->claim = array(
            array(
                'donation_date' => '2013-04-07',
                'title' => 'Mrs',
                'first_name' => 'Mary',
                'last_name' => 'Smith',
                'house_no' => '100',
                'postcode' => 'AB23 4CD',
                'amount' => 500.00,
                'sponsored' => true
            ),
            array(
                'donation_date' => '2013-04-15',
                'title' => null,
                'first_name' => 'Jim',
                'last_name' => 'Harris',
                'house_no' => '25 High St Anytown Farmshire',
                'postcode' => null,
                'amount' => 10.00
            ),
            array(
                'donation_date' => '2013-04-17',
                'title' => null,
                'first_name' => 'Bill',
                'last_name' => 'Hill-Jones',
                'house_no' => '1',
                'postcode' => 'BA23 9CD',
                'amount' => 2.50
            ),
            array(
                'donation_date' => '2013-04-20',
                'title' => null,
                'first_name' => 'Bob',
                'last_name' => 'Hill-Jones',
                'house_no' => '1',
                'postcode' => 'BA23 9CD',
                'amount' => 12.00
            ),
            array(
                'donation_date' => '2013-04-20',
                'amount' => 1000.00,
                'aggregation' => 'Aggregated donation of 200 x £5 payments from members'
            )
        );

        /**
         * The following call sets up the service object used to interact with the
         * Government Gateway. Setting parameter 4 to null will force the test to
         * use the httpClient created on the fly within the GovTalk class and may
         * also effectively disable mockability.
         * Set parameter 5 to a valid path in order to log messages
         */
        $this->gaService = $this->setUpService();
    }

    private function setUpService()
    {
        return new GiftAid(
            $this->gatewayUserID,
            $this->gatewayUserPassword,
            $this->gatewayVendorID,
            $this->gatewaySoftware,
            $this->gatewaySoftVersion,
            true,
            $this->getHttpClient()
        );
    }

    public function testServiceCreation()
    {
        $this->gaService->setAgentDetails('company', array('ln1','ln2','pc'), array('07123456789'));
        $this->assertInstanceOf('GovTalk\GiftAid\GiftAid', $this->gaService);
    }

    public function testCharityId()
    {
        $value = uniqid();
        $this->gaService->setCharityId($value);
        $this->assertSame($value, $this->gaService->getCharityId());
    }

    public function testVendorId()
    {
        $value = uniqid();
        $this->gaService->setVendorId($value);
        $this->assertSame($value, $this->gaService->getVendorId());
    }

    public function testProductUri()
    {
        $value = uniqid();
        $this->gaService->setProductUri($value);
        $this->assertSame($value, $this->gaService->getProductUri());
    }

    public function testProductName()
    {
        $value = uniqid();
        $this->gaService->setProductName($value);
        $this->assertSame($value, $this->gaService->getProductName());
    }

    public function testProductVersion()
    {
        $value = uniqid();
        $this->gaService->setProductVersion($value);
        $this->assertSame($value, $this->gaService->getProductVersion());
    }

    public function testConnectedCharities()
    {
        $this->gaService->setConnectedCharities(false);
        $this->assertFalse($this->gaService->getConnectedCharities());

        $this->gaService->setConnectedCharities(true);
        $this->assertTrue($this->gaService->getConnectedCharities());

        // non-bool values are treated as false
        $this->gaService->setConnectedCharities('1');
        $this->assertFalse($this->gaService->getConnectedCharities());
    }

    public function testCommunityBuildings()
    {
        $this->gaService->setCommunityBuildings(false);
        $this->assertFalse($this->gaService->getCommunityBuildings());

        $this->gaService->setCommunityBuildings(true);
        $this->assertTrue($this->gaService->getCommunityBuildings());

        // non-bool values are treated as false
        $this->gaService->setCommunityBuildings('1');
        $this->assertFalse($this->gaService->getCommunityBuildings());
    }

    public function testCbcd()
    {
        $this->gaService->addCbcd('bldg', 'address', 'postcode', '2014', 12.34);
        $this->gaService->resetCbcd();
    }

    public function testClaimToDate()
    {
        $value = uniqid();
        $this->gaService->setClaimToDate($value);
        $this->assertSame($value, $this->gaService->getClaimToDate());
    }

    public function testAuthorisedOfficial()
    {
        $this->gaService->setAuthorisedOfficial($this->officer);
        $this->assertSame($this->officer, $this->gaService->getAuthorisedOfficial());
    }

    public function testClaimingOrganisation()
    {
        $this->gaService->setClaimingOrganisation($this->claimant);
        $this->assertSame($this->claimant, $this->gaService->getClaimingOrganisation());
    }

    public function testEndpoint()
    {
        $testEndpoint = $this->gaService->getEndpoint(true);
        $liveEndpoint = $this->gaService->getEndpoint(false);

        $this->assertNotSame($liveEndpoint, $testEndpoint);
    }

    public function testAdjustments()
    {
        $clear = array('amount' => 0.00, 'reason' => '');
        $adjust = array('amount' => 16.47, 'reason' => 'Refunds issued on previous donations.');

        $this->gaService->setGaAdjustment(
            $adjust['amount'],
            $adjust['reason']
        );
        $this->assertSame($adjust, $this->gaService->getGaAdjustment());

        $this->gaService->clearGaAdjustment();
        $this->assertSame($clear, $this->gaService->getGaAdjustment());
    }

    public function testGasds()
    {
        $clear = array('amount' => 0.00, 'reason' => '');
        $adjust = array('amount' => 16.47, 'reason' => 'Refunds issued on previous GASDS donations.');

        $this->gaService->setGasdsAdjustment(
            $adjust['amount'],
            $adjust['reason']
        );
        $this->assertSame($adjust, $this->gaService->getGasdsAdjustment());

        $this->gaService->setGasdsAdjustment(
            $clear['amount'],
            $clear['reason']
        );
        $this->assertSame($clear, $this->gaService->getGasdsAdjustment());

        $this->gaService->addGasds('2014', 15.26);
        $this->gaService->resetGasds();
    }

    public function testCompress()
    {
        $this->gaService->setCompress(false);
        $this->assertFalse($this->gaService->getCompress());

        $this->gaService->setCompress(true);
        $this->assertTrue($this->gaService->getCompress());

        // non-bool values are treated as false
        $this->gaService->setCompress('1');
        $this->assertFalse($this->gaService->getCompress());
    }

    public function testClaimSubmissionAuthFailure()
    {
        $this->setMockHttpResponse('SubmitAuthFailureResponse.txt');

        $this->gaService->setAuthorisedOfficial($this->officer);
        $this->gaService->setClaimingOrganisation($this->claimant);
        $response = $this->gaService->giftAidSubmit($this->claim);

        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('fatal', $response['errors']);
        $this->assertSame('1046', $response['errors']['fatal'][0]['number']);
        $this->assertSame(
            'Authentication Failure. The supplied user credentials failed validation for the requested service.',
            $response['errors']['fatal'][0]['text']
        );
    }

    public function testClaimSubmissionAck()
    {
        $this->setMockHttpResponse('SubmitAckResponse.txt');

        $this->gaService->setAuthorisedOfficial($this->officer);
        $this->gaService->setClaimingOrganisation($this->claimant);
        $response = $this->gaService->giftAidSubmit($this->claim);

        $this->assertArrayNotHasKey('errors', $response);
        //$this->assertSame('acknowledgement', $this->gaService->getResponseQualifier());
        $this->assertArrayHasKey('correlationid', $response);
        $this->assertArrayHasKey('endpoint', $response);
        $this->assertArrayHasKey('interval', $response);
        $this->assertSame('A19FA1A31BCB42D887EA323292AACD88', $response['correlationid']);
    }

    public function testDeclarationResponsePoll()
    {
        $this->setMockHttpResponse('DeclarationResponsePoll.txt');

        $response = $this->gaService->declarationResponsePoll(
            'A19FA1A31BCB42D887EA323292AACD88',
            'https://secure.dev.gateway.gov.uk/poll'
        );

        $this->assertArrayNotHasKey('errors', $response);
        //$this->assertSame('response', $this->gaService->getResponseQualifier());
        $this->assertArrayHasKey('correlationid', $response);
        $this->assertSame('A19FA1A31BCB42D887EA323292AACD88', $response['correlationid']);
    }

    public function testRequestClaimData()
    {
        $this->setMockHttpResponse('RequestClaimDataResponse.txt');

        $this->gaService->setAuthorisedOfficial($this->officer);
        $this->gaService->setClaimingOrganisation($this->claimant);
        $response = $this->gaService->requestClaimData();

        $this->assertArrayNotHasKey('errors', $response);
    }

    public function testDeleteRequest()
    {
        $this->setMockHttpResponse('DeleteResponse.txt');

        $this->gaService->setAuthorisedOfficial($this->officer);
        $this->gaService->setClaimingOrganisation($this->claimant);
        $response = $this->gaService->sendDeleteRequest(
            'BE6622CBCA354E77A5A10BC24C29A0A7',
            'HMRC-CHAR-CLM'
        );

        $this->assertTrue($response);
    }
}
