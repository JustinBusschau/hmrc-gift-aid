<?php

/*
 * This file is part of the GovTalk package
 *
 * (c) Justin Busschau
 *
 * For the full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 */

namespace GovTalk\GiftAid;

use PHPUnit_Framework_TestCase;
use ReflectionObject;
use Guzzle\Common\Event;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\RequestInterface as GuzzleRequestInterface;
use Guzzle\Plugin\Mock\MockPlugin;

/**
 * The Base class for all tests
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    private $httpClient;

    /**
     * Create an instance of the Guzzle HTTP Client that we can
     * throw mocked responses into.
     */
    public function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new HttpClient;
        }

        return $this->httpClient;
    }

    /**
     * Mark a request as being mocked
     *
     * @param GuzzleRequestInterface $request
     * @return self
     */
    public function addMockedHttpRequest(GuzzleRequestInterface $request)
    {
        $this->mockHttpRequests[] = $request;

        return $this;
    }

    /**
     * Get a mock response for a client by mock file name
     *
     * @param string $path Relative path to the mock response file
     * @return Response
     */
    public function getMockHttpResponse($path)
    {
        if ($path instanceof Response) {
            return $path;
        }

        $ref = new ReflectionObject($this);
        $dir = dirname($ref->getFileName());

        // if mock file doesn't exist, check parent directory
        if (!file_exists($dir.'/Mock/'.$path) && file_exists($dir.'/../Mock/'.$path)) {
            return MockPlugin::getMockFile($dir.'/../Mock/'.$path);
        }

        return MockPlugin::getMockFile($dir.'/Mock/'.$path);
    }

    /**
     * Set a mock response from a mock file for the next client request.
     *
     * @param string $paths Path to the mock response file
     * @return MockPlugin returns the mock plugin
     */
    public function setMockHttpResponse($paths)
    {
        $this->mockHttpRequests = array();
        $that = $this;
        $mock = new MockPlugin(null, true);
        $this->getHttpClient()->getEventDispatcher()->removeSubscriber($mock);
        $mock->getEventDispatcher()->addListener('mock.request', function(Event $event) use ($that) {
            $that->addMockedHttpRequest($event['request']);
        });

        foreach ((array) $paths as $path) {
            $mock->addResponse($this->getMockHttpResponse($path));
        }

        $this->getHttpClient()->getEventDispatcher()->addSubscriber($mock);

        return $mock;
    }
}
