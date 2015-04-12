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

use XMLWriter;
use DOMDocument;
use Guzzle\Http\ClientInterface;
use GovTalk\GovTalk;
use GovTalk\GiftAid\Individual;
use GovTalk\GiftAid\AuthorisedOfficial;

/**
 * HMRC Gift Aid API client.  Extends the functionality provided by the
 * GovTalk class to build and parse HMRC Gift Aid submissions.
 *
 * @author    Long Luong
 * @copyright 2013, Veda Consulting Limited
 * @licence http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 *
 * @author    Justin Busschau
 * @copyright 2013 - 2014, Justin Busschau
 * Refactored as PSR-2 for inclusion in justinbusschau/php-govtalk package.
 */
class GiftAid extends GovTalk
{
    /* General IRenvelope related variables. */

    /**
     * Endpoints - One for test/dev and one for the live environment
     */
    private $devEndpoint  = 'https://secure.dev.gateway.gov.uk/submission';
    private $liveEndpoint = 'https://secure.gateway.gov.uk/submission';

    /**
     * Vendor ID of software vendor
     *
     * @var string
     */
    private $vendorId = '';

    /**
     * URI for product submitting the claim
     *
     * @var string
     */
    private $productUri = '';

    /**
     * Name of product submitting the claim
     *
     * @var string
     */
    private $productName = '';

    /**
     * Version of product submitting the claim
     *
     * @var string
     */
    private $productVersion = '';

    /**
     * Details of the agent sending the return declaration.
     *
     * @var string
     */
    private $agentDetails = array();

    /* System / internal variables. */

    /**
     * Flag indicating if the IRmark should be generated for outgoing XML.
     *
     * @var boolean
     */
    private $generateIRmark = true;

    /* Variables for storing claim details */

    /**
     * Adjustments
     */
    private $gaAdjustment = 0.00;
    private $gaAdjReason  = '';

    /**
     * Connected charities
     */
    private $connectedCharities = false;
    private $communityBuildings = false;

    /**
     * Claiming organisation
     */
    private $claimingOrganisation = null;

    /**
     * Authorised official
     */
    private $authorisedOfficial = null;

    /**
     * Date of most recent claim
     */
    private $claimToDate = '';

    /**
     * Should we use compression on submitted claim?
     */
    private $compress = true;

    /**
     * Details of the Community buildings used
     */
    private $haveCbcd   = false;
    private $cbcdBldg   = array();
    private $cbcdAddr   = array();
    private $cbcdPoCo   = array();
    private $cbcdYear   = array();
    private $cbcdAmount = array();

    /**
     * Details for claims relating to the Small Donations Scheme
     */
    private $haveGasds       = false;
    private $gasdsYear       = array();
    private $gasdsAmount     = array();
    private $gasdsAdjustment = 0.00;
    private $gasdsAdjReason  = '';

    /**
     * The class is instantiated with the 'SenderID' and password issued to the
     * claiming charity by HMRC. Also we need to know whether messages for
     * this session are to be sent to the test or live environment
     *
     * @param $sender_id          The govTalk Sender ID as provided by HMRC
     * @param $password           The govTalk password as provided by HMRC
     * @param $route_uri          The URI of the owner of the process generating this route entry.
     * @param $software_name      The name of the software generating this route entry.
     * @param $software_version   The version number of the software generating this route entry.
     * @param $test               TRUE if in test mode, else (default) FALSE
     * @param $httpClient         The Guzzle HTTP Client to use for connections to the endpoint - null for default
     * @param $messageLogLocation Where to log messages - null for no logging
     */
    public function __construct(
        $sender_id,
        $password,
        $route_uri,
        $software_name,
        $software_version,
        $test = false,
        ClientInterface $httpClient = null,
        $messageLogLocation = null
    ) {
        $test = is_bool($test) ? $test : false;

        $endpoint = $this->getEndpoint($test);

        $this->setProductUri($route_uri);
        $this->setProductName($software_name);
        $this->setProductVersion($software_version);
        $this->setTestFlag($test);

        parent::__construct(
            $endpoint,
            $sender_id,
            $password,
            $httpClient,
            $messageLogLocation
        );

        $this->setMessageAuthentication('clear');
    }

    /**
     * Find out which endpoint to use
     *
     * @param $test TRUE if in test mode, else (default) FALSE
     */
    public function getEndpoint($test = false)
    {
        $test = is_bool($test) ? $test : false;

        return $test ? $this->devEndpoint : $this->liveEndpoint;
    }

    /**
     * Some getters and setters for our internal properties.
     */
    public function getCharityId()
    {
        if ( ! is_null($this->getClaimingOrganisation())) {
            return $this->getClaimingOrganisation()->getHmrcRef();
        } else {
            return false;
        }
    }

    public function setCharityId($value)
    {
        if (is_null($this->getClaimingOrganisation())) {
            $this->setClaimingOrganisation(
                new ClaimingOrganisation()
            );
        }
        $this->getClaimingOrganisation()->setHmrcRef($value);
    }

    public function getVendorId()
    {
        return $this->vendorId;
    }

    public function setVendorId($value)
    {
        $this->vendorId = $value;
    }

    public function getProductUri()
    {
        return $this->productUri;
    }

    public function setProductUri($value)
    {
        $this->productUri = $value;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function setProductName($value)
    {
        $this->productName = $value;
    }

    public function getProductVersion()
    {
        return $this->productVersion;
    }

    public function setProductVersion($value)
    {
        $this->productVersion = $value;
    }

    public function clearGaAdjustment()
    {
        $this->gaAdjustment = 0.00;
        $this->gaAdjReason  = '';
    }

    public function setGaAdjustment($amount, $reason)
    {
        $this->gaAdjustment = $amount;
        $this->gaAdjReason  = $reason;
    }

    public function getGaAdjustment()
    {
        return array('amount' => $this->gaAdjustment, 'reason' => $this->gaAdjReason);
    }

    public function getConnectedCharities()
    {
        return $this->connectedCharities;
    }

    public function setConnectedCharities($value)
    {
        if (is_bool($value)) {
            $this->connectedCharities = $value;
        } else {
            $this->connectedCharities = false;
        }
    }

    public function getCommunityBuildings()
    {
        return $this->communityBuildings;
    }

    public function setCommunityBuildings($value)
    {
        if (is_bool($value)) {
            $this->communityBuildings = $value;
        } else {
            $this->communityBuildings = false;
        }
    }

    public function getClaimingOrganisation()
    {
        return $this->claimingOrganisation;
    }

    public function setClaimingOrganisation(ClaimingOrganisation $value)
    {
        $this->claimingOrganisation = $value;
    }

    public function getAuthorisedOfficial()
    {
        return $this->authorisedOfficial;
    }

    public function setAuthorisedOfficial(AuthorisedOfficial $value)
    {
        $this->authorisedOfficial = $value;
    }

    public function getClaimToDate()
    {
        return $this->claimToDate;
    }

    public function setClaimToDate($value)
    {
        $this->claimToDate = $value;
    }

    public function getCompress()
    {
        return $this->compress;
    }

    public function setCompress($value)
    {
        if (is_bool($value)) {
            $this->compress = $value;
        } else {
            $this->compress = false;
        }
    }

    public function addCbcd($bldg, $address, $postcode, $year, $amount)
    {
        $this->haveCbcd     = true;
        $this->cbcdBldg[]   = $bldg;
        $this->cbcdAddr[]   = $address;
        $this->cbcdPoCo[]   = $postcode;
        $this->cbcdYear[]   = $year;
        $this->cbcdAmount[] = $amount;
    }

    public function resetCbcd()
    {
        $this->haveCbcd   = false;
        $this->cbcdBldg   = array();
        $this->cbcdAddr   = array();
        $this->cbcdPoCo   = array();
        $this->cbcdYear   = array();
        $this->cbcdAmount = array();
    }

    public function addGasds($year, $amount)
    {
        $this->haveGasds     = true;
        $this->gasdsYear[]   = $year;
        $this->gasdsAmount[] = $amount;
    }

    public function resetGasds()
    {
        $this->haveGasds   = false;
        $this->gasdsYear   = array();
        $this->gasdsAmount = array();
    }

    public function setGasdsAdjustment($amount, $reason)
    {
        $this->gasdsAdjustment = $amount;
        $this->gasdsAdjReason  = $reason;
    }

    public function getGasdsAdjustment()
    {
        return array('amount' => $this->gasdsAdjustment, 'reason' => $this->gasdsAdjReason);
    }

    /**
     * Sets details about the agent submitting the declaration.
     *
     * The agent company's address should be specified in the following format:
     *   line => Array, each element containing a single line information.
     *   postcode => The agent company's postcode.
     *   country => The agent company's country. Defaults to England.
     *
     * The agent company's primary contact should be specified as follows:
     *   name => Array, format as follows:
     *     title => Contact's title (Mr, Mrs, etc.)
     *     forename => Contact's forename.
     *     surname => Contact's surname.
     *   email => Contact's email address (optional).
     *   telephone => Contact's telephone number (optional).
     *   fax => Contact's fax number (optional).
     *
     * @param string $company   The agent company's name.
     * @param array  $address   The agent company's address in the format specified above.
     * @param array  $contact   The agent company's key contact (optional, may be skipped with a null value).
     * @param string $reference An identifier for the agent's own reference (optional).
     */
    public function setAgentDetails($company, array $address, array $contact = null, $reference = null)
    {
        if (preg_match('/[A-Za-z0-9 &\'\(\)\*,\-\.\/]*/', $company)) {
            $this->agentDetails['company'] = $company;
            $this->agentDetails['address'] = $address;
            if ( ! isset($this->agentDetails['address']['country'])) {
                $this->agentDetails['address']['country'] = 'England';
            }
            if ($contact !== null) {
                $this->agentDetails['contact'] = $contact;
            }
            if (($reference !== null) && preg_match('/[A-Za-z0-9 &\'\(\)\*,\-\.\/]*/', $reference)) {
                $this->agentDetails['reference'] = $reference;
            }
        } else {
            return false;
        }
    }

    /**
     * Takes the $donor_data array as supplied to $this->giftAidSubmit
     * and adds it into the $package XMLWriter document.
     *
     * $donor_data structure is as follows
     * 'donation_date',
     * 'title',
     * 'first_name',
     * 'last_name',
     * 'house_no',
     * 'postcode', - must be a uk postcode for any uk address
     * 'overseas', - must be true if no postcode provided
     * 'sponsored' - set to true if this money is for a sponsored event
     * 'aggregation' - description of aggregated donations - else leave empty
     * 'amount'
     *
     * @param array $donor_data
     */
    private function buildClaimXml($donor_data)
    {
        $package = new XMLWriter();
        $package->openMemory();
        $package->setIndent(true);

        $package->startElement('Claim');
        $package->writeElement('OrgName', $this->getClaimingOrganisation()->getName());
        $package->writeElement('HMRCref', $this->getClaimingOrganisation()->getHmrcRef());

        $package->startElement('Regulator');
        $package->writeElement('RegName', $this->getClaimingOrganisation()->getRegulator());
        $package->writeElement('RegNo', $this->getClaimingOrganisation()->getRegNo());
        $package->endElement(); # Regulator

        $package->startElement('Repayment');
        $earliestDate = strtotime(date('Y-m-d'));
        foreach ($donor_data as $d) {
            if (isset($d['donation_date'])) {
                $dDate        = strtotime($d['donation_date']);
                $earliestDate = ($dDate < $earliestDate) ? $dDate : $earliestDate;
            }
            $package->startElement('GAD');
            if ( ! isset($d['aggregation']) or empty($d['aggregation'])) {
                $package->startElement('Donor');
                $person = new Individual(
                    $d['title'],
                    $d['first_name'],
                    $d['last_name'],
                    '',
                    $d['house_no'],
                    $d['postcode'],
                    (bool) $d['overseas']
                );

                $title    = $person->getTitle();
                $fore     = $person->getForename();
                $sur      = $person->getSurname();
                $house    = $person->getHouseNum();
                $postcode = $person->getPostcode();

                if (!empty($title)) {
                    $package->writeElement('Ttl', $title);
                }
                if (!empty($fore)) {
                    $package->writeElement('Fore', $fore);
                }
                if (!empty($sur)) {
                    $package->writeElement('Sur', $sur);
                }
                if (!empty($house)) {
                    $package->writeElement('House', $house);
                }
                if (!empty($postcode)) {
                    $package->writeElement('Postcode', $postcode);
                }
                $package->endElement(); # Donor
            } elseif ( ! empty($d['aggregation'])) {
                $package->writeElement('AggDonation', $d['aggregation']);
            }
            if (isset($d['sponsored']) and $d['sponsored'] === true) {
                $package->writeElement('Sponsored', 'yes');
            }
            $package->writeElement('Date', $d['donation_date']);
            $package->writeElement('Total', number_format($d['amount'], 2, '.', ''));
            $package->endElement(); # GAD
        }
        $package->writeElement('EarliestGAdate', date('Y-m-d', $earliestDate));

        if ( ! empty($this->gaAdjustment)) {
            $package->writeElement('Adjustment', number_format($this->gaAdjustment, 2, '.', ''));
        }
        $package->endElement(); # Repayment

        $package->startElement('GASDS');
        $package->writeElement(
            'ConnectedCharities',
            $this->getClaimingOrganisation()->getHasConnectedCharities() ? 'yes' : 'no'
        );
        foreach ($this->getClaimingOrganisation()->getConnectedCharities() as $cc) {
            $package->startElement('Charity');
            $package->writeElement('Name', $cc->getName());
            $package->writeElement('HMRCref', $cc->getHmrcRef());
            $package->endElement(); # Charity
        }
        foreach ($this->gasdsYear as $key => $val) {
            $package->startElement('GASDSClaim');
            $package->writeElement('Year', $this->gasdsYear[$key]);
            $package->writeElement('Amount', number_format($this->gasdsAmount[$key], 2, '.', ''));
            $package->endElement(); # GASDSClaim
        }

        $package->writeElement('CommBldgs', ($this->haveCbcd == true) ? 'yes' : 'no');
        foreach ($this->cbcdAddr as $key => $val) {
            $package->startElement('Building');
            $package->writeElement('BldgName', $this->cbcdBldg[$key]);
            $package->writeElement('Address', $this->cbcdAddr[$key]);
            $package->writeElement('Postcode', $this->cbcdPoCo[$key]);
            $package->startElement('BldgClaim');
            $package->writeElement('Year', $this->cbcdYear[$key]);
            $package->writeElement('Amount', number_format($this->cbcdAmount[$key], 2, '.', ''));
            $package->endElement(); # BldgClaim
            $package->endElement(); # Building
        }

        if ( ! empty($this->gasdsAdjustment)) {
            $package->writeElement('Adj', number_format($this->gasdsAdjustment, 2, '.', ''));
        }

        $package->endElement(); # GASDS

        $otherInfo = array();
        if ( ! empty($this->gasdsAdjustment)) {
            $otherInfo[] = $this->gasdsAdjReason;
        }
        if ( ! empty($this->gaAdjustment)) {
            $otherInfo[] = $this->gaAdjReason;
        }
        if (count($otherInfo) > 0) {
            $package->writeElement('OtherInfo', implode(' AND ', $otherInfo));
        }

        $package->endElement(); # Claim

        return $package->outputMemory();
    }

    /**
     * Submit a GA Claim - this is the crux of the biscuit.
     *
     * @param array $donor_data
     */
    public function giftAidSubmit($donor_data)
    {
        $cChardId      = $this->getClaimingOrganisation()->getHmrcRef();
        $cOrganisation = 'IR';

        $dReturnPeriod = $this->getClaimToDate();

        $sDefaultCurrency = 'GBP'; // currently HMRC only allows GBP
        $sIRmark          = 'IRmark+Token';
        $sSender          = 'Individual';

        if ($this->getAuthorisedOfficial() == null) {
            return false;
        }

        // Set the message envelope
        $this->setMessageClass('HMRC-CHAR-CLM');
        $this->setMessageQualifier('request');
        $this->setMessageFunction('submit');
        $this->setMessageCorrelationId(null);
        $this->setMessageTransformation('XML');
        $this->addTargetOrganisation($cOrganisation);

        $this->addMessageKey('CHARID', $cChardId);

        $this->addChannelRoute(
            $this->getProductUri(),
            $this->getProductName(),
            $this->getProductVersion()
        );

        // Build message body...
        $package = new XMLWriter();
        $package->openMemory();
        $package->setIndent(true);

        $package->startElement('IRenvelope');
        $package->writeAttribute('xmlns', 'http://www.govtalk.gov.uk/taxation/charities/r68/2');

        $package->startElement('IRheader');
        $package->startElement('Keys');
        $package->startElement('Key');
        $package->writeAttribute('Type', 'CHARID');
        $package->text($cChardId);
        $package->endElement(); # Key
        $package->endElement(); # Keys
        $package->writeElement('PeriodEnd', $dReturnPeriod);
        $package->writeElement('DefaultCurrency', $sDefaultCurrency);
        $package->startElement('IRmark');
        $package->writeAttribute('Type', 'generic');
        $package->text($sIRmark);
        $package->endElement(); #IRmark
        $package->writeElement('Sender', $sSender);
        $package->endElement(); #IRheader

        $package->startElement('R68');
        $package->startElement('AuthOfficial');
        $package->startElement('OffName');
        $title = $this->getAuthorisedOfficial()->getTitle();
        if ( ! empty($title)) {
            $package->writeElement('Ttl', $title);
        }
        $package->writeElement('Fore', $this->getAuthorisedOfficial()->getForename());
        $package->writeElement('Sur', $this->getAuthorisedOfficial()->getSurname());
        $package->endElement(); #OffName
        $package->startElement('OffID');
        $package->writeElement('Postcode', $this->getAuthorisedOfficial()->getPostcode());
        $package->endElement(); #OffID
        $package->writeElement('Phone', $this->getAuthorisedOfficial()->getPhone());
        $package->endElement(); #AuthOfficial
        $package->writeElement('Declaration', 'yes');

        $claimDataXml = $this->buildClaimXml($donor_data, false);
        if ($this->compress == true) {
            $package->startElement('CompressedPart');
            $package->writeAttribute('Type', 'gzip');
            $package->text(base64_encode(gzencode($claimDataXml, 9, FORCE_GZIP)));
            $package->endElement(); # CompressedPart
        } else {
            $package->writeRaw($claimDataXml);
        }

        $package->endElement(); #R68
        $package->endElement(); #IRenvelope

        // Send the message and deal with the response...
        $this->setMessageBody($package);

        if ($this->sendMessage() && ($this->responseHasErrors() === false)) {
            $returnable                  = $this->getResponseEndpoint();
            $returnable['correlationid'] = $this->getResponseCorrelationId();
        } else {
            $returnable = array('errors' => $this->getResponseErrors());
        }
        $returnable['claim_data_xml']     = $claimDataXml;
        $returnable['submission_request'] = $this->fullRequestString;

        return $returnable;
    }

    /**
     * Submit a request for GA Claim Data
     */
    public function requestClaimData()
    {
        $this->setMessageClass('HMRC-CHAR-CLM');
        $this->setMessageQualifier('request');
        $this->setMessageFunction('list');
        $this->setMessageCorrelationId('');
        $this->setMessageTransformation('XML');

        $this->addTargetOrganisation('IR');

        $this->addMessageKey('CHARID', $this->getClaimingOrganisation()->getHmrcRef());

        $this->addChannelRoute(
            $this->getProductUri(),
            $this->getProductName(),
            $this->getProductVersion()
        );

        $this->setMessageBody('');

        if ($this->sendMessage() && ($this->responseHasErrors() === false)) {
            $returnable = $this->getResponseEndpoint();
            foreach ($this->fullResponseObject->Body->StatusReport->StatusRecord as $node) {
                $array = array();
                foreach ($node->children() as $child) {
                    $array[$child->getName()] = (string) $child;
                }
                $returnable['statusRecords'][] = $array;
            }
        } else {
            $returnable = array('errors' => $this->getResponseErrors());
        }
        $returnable['submission_request'] = $this->fullRequestString;

        return $returnable;
    }

    /**
     * Polls the Gateway for a submission response / error following a VAT
     * declaration request. By default the correlation ID from the last response
     * is used for the polling, but this can be over-ridden by supplying a
     * correlation ID. The correlation ID can be skipped by passing a null value.
     *
     * If the resource is still pending this method will return the same array
     * as declarationRequest() -- 'endpoint', 'interval' and 'correlationid' --
     * if not then it'll return lots of useful information relating to the return
     * and payment of any VAT due in the following array format:
     *
     *  message => an array of messages ('Thank you for your submission', etc.).
     *  accept_time => the time the submission was accepted by the HMRC server.
     *  period => an array of information relating to the period of the return:
     *    id => the period ID.
     *    start => the start date of the period.
     *    end => the end date of the period.
     *  payment => an array of information relating to the payment of the return:
     *    narrative => a string representation of the payment (generated by HMRC)
     *    netvat => the net value due following this return.
     *    payment => an array of information relating to the method of payment:
     *      method => the method to be used to pay any money due, options are:
     *        - nilpayment: no payment is due.
     *        - repayment: a repayment from HMRC is due.
     *        - directdebit: payment will be taken by previous direct debit.
     *        - payment: payment should be made by alternative means.
     *      additional => additional information relating to this payment.
     *
     * @param string $correlationId The correlation ID of the resource to poll. Can be skipped with a null value.
     * @param string $pollUrl       The URL of the Gateway to poll.
     *
     * @return mixed An array of details relating to the return and the original request, or false on failure.
     */
    public function declarationResponsePoll($correlationId = null, $pollUrl = null)
    {
        if ($correlationId === null) {
            $correlationId = $this->getResponseCorrelationId();
        }

        if ($this->setMessageCorrelationId($correlationId)) {
            if ($pollUrl !== null) {
                $this->setGovTalkServer($pollUrl);
            }
            $this->setMessageClass('HMRC-CHAR-CLM');
            $this->setMessageQualifier('poll');
            $this->setMessageFunction('submit');
            $this->setMessageTransformation('XML');
            $this->resetMessageKeys();
            $this->setMessageBody('');
            if ($this->sendMessage() && ($this->responseHasErrors() === false)) {
                $messageQualifier = (string) $this->fullResponseObject->Header->MessageDetails->Qualifier;
                if ($messageQualifier == 'response') {
                    return array(
                        'correlationid'       => $correlationId,
                        'submission_request'  => $this->fullRequestString,
                        'submission_response' => $this->fullResponseString
                    );

                } elseif ($messageQualifier == 'acknowledgement') {
                    $returnable                       = $this->getResponseEndpoint();
                    $returnable['correlationid']      = $this->getResponseCorrelationId();
                    $returnable['submission_request'] = $this->fullRequestString;

                    return $returnable;
                } else {
                    return false;
                }
            } else {
                if ($this->responseHasErrors()) {
                    return array(
                        'errors'             => $this->getResponseErrors(),
                        'fullResponseString' => $this->fullResponseString
                    );
                }

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Adds a valid IRmark to the given package.
     *
     * This function over-rides the packageDigest() function provided in the main
     * php-govtalk class.
     *
     * @param string $package The package to add the IRmark to.
     *
     * @return string The new package after addition of the IRmark.
     */
    protected function packageDigest($package)
    {
        $packageSimpleXML  = simplexml_load_string($package);
        $packageNamespaces = $packageSimpleXML->getNamespaces();

        $body = $packageSimpleXML->xpath('GovTalkMessage/Body');

        preg_match('#<Body>(.*)<\/Body>#su', $packageSimpleXML->asXML(), $matches);
        $packageBody = $matches[1];

        $irMark  = base64_encode($this->generateIRMark($packageBody, $packageNamespaces));
        $package = str_replace('IRmark+Token', $irMark, $package);

        return $package;
    }

    /**
     * Generates an IRmark hash from the given XML string for use in the IRmark
     * node inside the message body.  The string passed must contain one IRmark
     * element containing the string IRmark (ie. <IRmark>IRmark+Token</IRmark>) or the
     * function will fail.
     *
     * @param $xmlString string The XML to generate the IRmark hash from.
     *
     * @return string The IRmark hash.
     */
    private function generateIRMark($xmlString, $namespaces = null)
    {
        if (is_string($xmlString)) {
            $xmlString = preg_replace(
                '/<(vat:)?IRmark Type="generic">[A-Za-z0-9\/\+=]*<\/(vat:)?IRmark>/',
                '',
                $xmlString,
                - 1,
                $matchCount
            );
            if ($matchCount == 1) {
                $xmlDom = new DOMDocument;

                if ($namespaces !== null && is_array($namespaces)) {
                    $namespaceString = array();
                    foreach ($namespaces as $key => $value) {
                        if ($key !== '') {
                            $namespaceString[] = 'xmlns:' . $key . '="' . $value . '"';
                        } else {
                            $namespaceString[] = 'xmlns="' . $value . '"';
                        }
                    }
                    $bodyCompiled = '<Body ' . implode(' ', $namespaceString) . '>' . $xmlString . '</Body>';
                } else {
                    $bodyCompiled = '<Body>' . $xmlString . '</Body>';
                }
                $xmlDom->loadXML($bodyCompiled);

                return sha1($xmlDom->documentElement->C14N(), true);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getResponseErrors()
    {
        $govTalkErrors = parent::getResponseErrors();

        foreach ($govTalkErrors['business'] as $b_index => $b_err) {
            if ($b_err['number'] == "3001") {
                unset($govTalkErrors['business'][$b_index]);
            }
        }

        $has_gt_errors = false;
        foreach ($govTalkErrors as $type) {
            if (count($type) > 0) {
                $has_gt_errors = true;
            }
        }

        if ( ! $has_gt_errors) {
            // lay out the GA errors
            foreach ($this->fullResponseObject->Body->ErrorResponse->Error as $gaError) {
                $govTalkErrors['business'][] = array(
                    'number'   => (string) $gaError->Number,
                    'text'     => (string) $gaError->Text,
                    'location' => (string) $gaError->Location
                );
            }
        }

        return $govTalkErrors;
    }
}
