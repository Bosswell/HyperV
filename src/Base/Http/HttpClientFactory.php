<?php

namespace App\Base\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class HttpClientFactory
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $httpLogger)
    {
        $this->logger = $httpLogger;
    }

    public function createClient(): HttpClientInterface
    {
        return new HttpClientDecorator(
            HttpClient::create(),
            $this->logger
        );
    }
}
