<?php

error_reporting(E_ALL | E_STRICT);

// composer autoloader
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('GovTalk\\GiftAid\\',__DIR__);
