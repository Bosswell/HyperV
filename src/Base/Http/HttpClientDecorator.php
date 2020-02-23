<?php

namespace App\Base\Http;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;


class HttpClientDecorator implements HttpClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $this->logger->info(sprintf(
            '%s %s %s',
            $method,
            $url,
            json_encode($options)
        ));

        /** @var ResponseInterface $response */
        $response = $this->httpClient->request(...func_get_args());

        $this->logger->info(sprintf(
            '%s',
            json_encode($response->getInfo()) ?? '',
        ));

        return $response;
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream(...func_get_args());
    }
}