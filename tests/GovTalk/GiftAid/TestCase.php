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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * The Base class for all tests
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?Client $httpClient = null;

    /**
     * Create an instance of the Guzzle HTTP Client that we can
     * throw mocked responses into.
     */
    public function getHttpClient(): Client
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    /**
     * Set a mock response from a mock file for the next client request.
     *
     * @param string  $path Path to the mock response file
     *
     * @link https://docs.guzzlephp.org/en/stable/testing.html#mock-handler
     */
    public function setMockHttpResponse(string $path): void
    {
        $mock = new MockHandler([
            $this->getMockHttpResponse($path),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->httpClient = new Client(['handler' => $handlerStack]);
    }

    /**
     * Get a mock response for a client by mock file name
     *
     * @param string $path Relative path to the mock response file
     * @return Response
     */
    protected function getMockHttpResponse(string $path): Response
    {
        return new Response(200, [], file_get_contents(__DIR__ . "/Mock/$path"));
    }
}
