<?php


namespace App\VehicleWebsite\Otomoto;


use App\Base\Http\HttpClientFactory;
use App\VehicleWebsite\AuctionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OtomotoClient
{
    /** @var HttpClientInterface */
    private $client;

    public function __construct(HttpClientFactory $httpClientFactory)
    {
        $this->client = $httpClientFactory->createClient();
    }

    /**
     * @return AuctionInterface[]
     */
    public function getAuctionsOverviews(): array
    {

    }

    private function execute()
    {
        try{
            $this->client->request('GET', '')
        } catch ()

    }
}