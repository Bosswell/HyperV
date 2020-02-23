<?php


namespace App\VehicleWebsite\Otomoto;


use App\Base\Http\HttpClientFactory;
use App\VehicleWebsite\AuctionOverviewProvider;
use Exception;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class MobileClient
{
    private HttpClientInterface $client;
    private string $host;

    public function __construct(HttpClientFactory $httpClientFactory)
    {
        $this->host = 'https://mobile.de/';
        $this->client = $httpClientFactory->createClient();
    }

    /**
     * @return AuctionOverviewProvider[]
     * @throws Throwable
     */
    public function getAuctionsOverviews(string $type, string $searchQuery, int $pageNumber): array
    {
        if (!in_array($type, self::AVAILABLE_TYPES)) {
            throw new InvalidArgumentException(sprintf(
                'Type [%s] is not available',
                $type
            ));
        }

        $url = sprintf(
            '/%s/q-%s/?%s',
            $type,
            $searchQuery,
            'page=' . $pageNumber
        );

        $htmlDoc = $this->execute($url);
        $builder = new OtomotoAuctionOverviewBuilder($htmlDoc, $this->host);

        return $builder->buildList();
    }

    /**
     * @param string $url
     * @return string
     * @throws Throwable
     */
    private function execute(string $url)
    {
        try{
            return $this->client->request('GET', $this->host . $url)->getContent();
        } catch (
        TransportExceptionInterface
        | ClientExceptionInterface
        | RedirectionExceptionInterface
        | ServerExceptionInterface $exception
        ) {
            throw new Exception($exception->getMessage());
        }
    }
}
