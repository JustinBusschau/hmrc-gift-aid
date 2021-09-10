# HMRC (Gift Aid) Charity Repayment Claims

**A library for charities and CASCs to claim Gift Aid (including Small Donations) from HMRC**

[![Build Status](https://travis-ci.org/thebiggive/hmrc-gift-aid.png?branch=main)](https://travis-ci.org/thebiggive/hmrc-gift-aid)
[![Latest Stable Version](https://poser.pugx.org/thebiggive/hmrc-gift-aid/version.png)](https://packagist.org/packages/thebiggive/hmrc-gift-aid)
[![Total Downloads](https://poser.pugx.org/thebiggive/hmrc-gift-aid/d/total.png)](https://packagist.org/packages/thebiggive/hmrc-gift-aid)
[![License](https://poser.pugx.org/thebiggive/hmrc-gift-aid/license.svg)](https://packagist.org/packages/thebiggive/hmrc-gift-aid)

'Gift Aid' is a UK tax incentive that enables tax-effective giving by individuals to charities
in the United Kingdom. Gift Aid increases the value of donations to charities and Community
Amateur Sports Clubs (CASCs) by allowing them to reclaim basic rate tax on a donor's gift.

'HMRC Charity Repayment Claims' is a library for submitting Gift Aid claims to HMRC.


## Installation

The library can be installed via [Composer](http://getcomposer.org/). To install, simply add
it to your `composer.json` file:

```json
{
    "require": {
        "thebiggive/hmrc-gift-aid": "^1.0"
    }
}
```

And run composer to update your dependencies:

$ curl -s http://getcomposer.org/installer | php
$ php composer.phar update


## Some notes on the library and Data Persistance

From the introduction to the [IRMark Specification](http://www.hmrc.gov.uk/softwaredevelopers/hmrcmark/generic-irmark-specification-v1-2.pdf):

> There is legislation in place that states that in the case of a civil dispute between the
> Inland Revenue (IR) and a taxpayer with regards to an Internet online submission, the
> submission held by the Inland Revenue is presumed to be correct unless the taxpayer can
> prove otherwise. In other words the burden of proof is on the taxpayer. There is therefore
> a requirement to enable the IR Online services and software that uses the services to provide
> a mechanism to aid a taxpayer to prove whether or not the submission held by IR is indeed the
> submission they sent.

That is a very roundabout way of saying the XML that you submit must include a signature of some
sort. The signature can be used to prove that what the HMRC received is actually what you
intended to send. HMRC will, in their turn, include a similar signature in any responses they
send to you. In the case of submissions to the HMRC Government Gateway, this signature is the
IRmark (pronounced IR Mark).

It is strongly recommended that both the XML that you send and the XML that you receive should
be stored in case there is any dispute over the claim - be that a dispute over the submission
of the claim or over the content of the claim itself.

This library will generate the appropriate IRmark signature for all outgoing messages and check
the IRmark on all incoming messages. This library, however, does not attempt to store or in any
way persist any data whatsoever. This means that your application will need to store a number of
pieces of information for use during dispute resolution. Having said that, it is not necessary
to store ALL messages sent to or received from the gateway. The following is a recommended set
of data to be stored by your application.

- **HMRC Correlation ID** This will be generated by HMRC when you send your request and returned
in all subsequent messages. You will also need to supply this correlation ID when submitting
any messages or queries related to the claim. While it is not essential to store this, I do
recommend it.

- **The Claim Request** The communication protocol requires a number of messages to be exchanged
in the course of a claim submission. I recommend storing only the initial claim request as this
is the message that will contain all the claim data. Other messages simply facilitate the
assured delivery of that initial message.

- **The Claim Response** This is not necessarily the first message you get back after sending
your Request - there will be polling and other protocol messages first. HMRC will first verify
the validity of the submitted claim (*__note__ this is verifying that the structure of the
message is valid and that the data conforms to the required standards*). Once this is done you
will receive a response message with an acknowledgement similar to this:
    ```
    HMRC has received the HMRC-CHAR-CLM document ref: AA12345 at 09.10 on 01/01/2014. The
    associated IRmark was: XXX9XXX9XXX9XXX9XXX9XXX9XXX9XXX9. We strongly recommend that you
    keep this receipt electronically, and we advise that you also keep your submission
    electronically for your records. They are evidence of the information that you submitted
    to HMRC.
    ```

See the sample source code below to see how and where to extract the above data from the
library.

## Basic Usage

### Preparing your data

The first thing you need is to identify both the organisation(s) and the individual
submitting the Gift Aid claim.

The `Vendor` data identifies the company and software product used to submit the claims. Each
vendor is assigned a Vendor ID and is required to identify the software that will submit the
claims. To obtain an ID, please see the
[Charities Online Service Recognition Process](http://www.hmrc.gov.uk/softwaredevelopers/gift-aid-repayments.htm#5).

```php
$vendor = [
    'id' => '4321',
    'product' => 'ProductNameHere',
    'version' => '0.1.2'
];
```

The `Authorised Official` is an individual within the organisation (Charity or CASC) that
has been previously identified to HMRC as having the authority to submit claims on behalf of
the organisation. That individual will register for an account to log in to Charities Online
and the user ID and password are required when submitting claims. The additional data sent
with the claim - name and contact details - must be consistent with that held by HMRC.

```php
$authorised_official = [
    'id' => '323412300001',
    'passwd' => 'testing1',
    'title' => 'Mr',
    'name' => 'Rex',
    'surname' => 'Muck',
    'phone' => '077 1234 5678',
    'postcode' => 'SW1A 1AA'
];
```

Each Charity or CASC that is registered with HMRC will have two identifiers. The first is the
`Charity ID` which is a number issued by HMRC when registering as a charity. The second is the
`Charities Commission Reference` which is issued by the relevant charity regulator. We also
need to know which regulator the charity is registered with.

```php
$charity = [
    'name' => 'A charitible organisation',
    'id' => 'AB12345',
    'reg_no' => '2584789658',
    'regulator' => 'CCEW'
];
```

Finally, you will need to build a list of all donations for which you want to claim a Gift Aid
repayment. For each donation you will also need to know the name and last known address of the
donor.

```php
$claim_items = [
    [
        'donation_date' => '2014-01-01',
        'title' => 'Mr',
        'first_name' => 'Jack',
        'last_name' => 'Peasant',
        'house_no' => '3',
        'postcode' => 'EC1A 2AB',
        'amount' => '123.45'
    ],
    [
        'donation_date' => '2014-01-01',
        'title' => 'Mrs',
        'first_name' => 'Josephine',
        'last_name' => 'Peasant',
        'house_no' => '3',
        'postcode' => 'EC1A 2AB',
        'amount' => '876.55'
    ],
];
```

And now that you have all the data you need, you can submit a claim.

### Preparing to send a request

This applies to all cases below. Whenever you need to send something to HMRC you will need to
prepare the gaService object as shown here.

```php
$gaService = new GiftAid(
    $authorised_official['id'],
    $authorised_official['passwd'],
    $vendor['id'],
    $vendor['product'],
    $vendor['version'],
    true        // Test mode. Leave this off or set to false for live claim submission
);

$gaService->setCharityId($charity['id']);
$gaService->setClaimToDate('2014-01-01'); // date of most recent donation

$gaService->setAuthorisedOfficial(
    new AuthorisedOfficial(
        $authorised_official['title'],
        $authorised_official['name'],
        $authorised_official['surname'],
        $authorised_official['phone'],
        $authorised_official['postcode']
    )
);

$gaService->setClaimingOrganisation(
    new ClaimingOrganisation(
        $charity['name'],
        $charity['id'],
        $charity['regulator'],
        $charity['reg_no']
    )
);
```

### Submitting a new claim

Once you have prepared the gaService object and collected your donations and donor data, you
are ready to send the claim.

```php
$gaService->setCompress(true);

$response = $gaService->giftAidSubmit($claim_items);

if (isset($response['errors'])) {
    // TODO: deal with the $response['errors']
} else {
    // giftAidSubmit returned no errors
    $correlation_id = $response['correlationid']; // TODO: store this !
    $endpoint = $response['endpoint'];
}

if ($correlation_id !== NULL) {
    $pollCount = 0;
    while ($pollCount < 3 and $response !== false) {
        $pollCount++;
        if (
            isset($response['interval']) and
            isset($response['endpoint']) and
            isset($response['correlationid'])
        ) {
            sleep($response['interval']);

            $response = $gaService->declarationResponsePoll(
                $response['correlationid'],
                $response['endpoint']
            );

            if (isset($response['errors'])) {
                // TODO: deal with the $response['errors']
            }

        } elseif (
            isset($response['correlationid']) and
            isset($response['submission_response'])
        ) {
            // TODO: store the submission_response and send the delete message
            $hmrc_response => $response['submission_response']; // TODO: store this !

            $response = !$gaService->sendDeleteRequest();
        }
    }
}
```

### Submitting adjustments with a claim

If you submit a claim and then subsequently need to reverse or refund a donation for which
you have already claimed Gift Aid, you will need to submit an adjustment with your next claim.
The adjustment value is set to the value of the refund you have already been paid for the
refunded donation. In other words if you claim Gift Aid on a £100.00 donation you will be paid
£25.00 by HMRC. If you subsequently refund that £100.00 you submit an adjustment to HMRC for
the £25.00.

Prepare the gaService object and your claim items as usual, but before calling `giftAidSubmit`
add the adjustment as shown below.

```php
// submit an adjustment to a previously submitted claim
$gaService->setGaAdjustment('34.89', 'Refunds issued on two previous donations.');
```

### Querying a previously submitted claim

Prepare the gaService object in the usual way and then call `requestClaimData`. This will
return a list of all previously submitted claims with status. It's a good idea to delete older
claim records - if nothing else it prevents having to download them all every time you need to
call `requestClaimData`.

```php
$response = $gaService->requestClaimData();
foreach ($response['statusRecords'] as $status_record) {
    // TODO: deal with the $status_record as you please

    if (
        $status_record['Status'] == 'SUBMISSION_RESPONSE' AND
        $status_record['CorrelationID'] != ''
    ) {
        $gaService->sendDeleteRequest($status_record['CorrelationID'], 'HMRC-CHAR-CLM');
    }
}
```


## More Information

For more information on the Gift Aid scheme as it applies to Charities and Community Amateur
Sports Clubs, and for information on Online Claim Submission, please see the
[Gov](https://www.gov.uk/charities-and-tax) website.

For information on developing and testing using HMRC Document Submission Protocol, please see
[Charities repayment claims support for software developers](https://www.gov.uk/government/collections/charities-online-support-for-software-developers).
