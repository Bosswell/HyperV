<?php


namespace App\VehicleWebsite\Otomoto;


use App\Base\Http\HttpClientFactory;
use App\VehicleWebsite\AuctionOverview;
use App\VehicleWebsite\VehicleWebsiteException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class OtomotoClient
{
    private HttpClientInterface $client;
    private string $host;

    public function __construct(HttpClientFactory $httpClientFactory)
    {
        $this->host = 'https://otomoto.pl/';
        $this->client = $httpClientFactory->createClient();
    }

    /**
     * @return AuctionOverview[]
     * @throws Throwable
     */
    public function getAuctionsOverviews(string $type, string $searchQuery, int $pageNumber): array
    {
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
            throw new VehicleWebsiteException($exception->getMessage());
        }
    }
}
