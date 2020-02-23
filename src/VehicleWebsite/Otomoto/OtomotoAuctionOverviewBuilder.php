<?php


namespace App\VehicleWebsite\Otomoto;

use App\Helper\ExceptionHelper;
use App\VehicleWebsite\AuctionOverview;
use Symfony\Component\DomCrawler\Crawler;

class OtomotoAuctionOverviewBuilder
{
    private string $htmlDoc;
    private string $host;

    public function __construct(string $htmlDoc, string $host)
    {
        $this->htmlDoc = $htmlDoc;
        $this->host = $host;
    }

    /**
     * @return AuctionOverview[]
     */
    public function buildList(): array
    {
        $crawler = new Crawler($this->htmlDoc, $this->host);
        $auctionsList = [];

        $auctions = $crawler
            ->filterXPath('html/body/div[4]/div[2]/section/div[2]/div[1]/div/div[1]/div[4]/article');

        if ($auctions->count() < 1) {
            return $auctionsList;
        }

        $auctions->each(function (Crawler $node) use (&$auctionsList) {
            $link = ExceptionHelper::handleExceptionAsString(fn () => $node->attr('data-href'));
            $name = ExceptionHelper::handleExceptionAsString(
                fn () => trim($node->filter('.offer-title')->text())
            );
            $shortDesc = ExceptionHelper::handleExceptionAsString(
                fn () => trim($node->filter('.offer-item__subtitle')->text())
            );
            $currency = ExceptionHelper::handleExceptionAsString(
                fn () => trim($node->filter('.offer-price__currency')->text())
            );
            $price = ExceptionHelper::handleExceptionAsString(
                fn () => trim($node->filter('.offer-price__number span')->text())
            );
            $image = ExceptionHelper::handleExceptionAsString(
                fn () => $node->filter('.offer-item__photo-link img')->attr('data-srcset')
            );

            /** @var Crawler $paramsNode */
            $paramsNode = ExceptionHelper::handleExceptionAsString(
                fn () => $node->filter('.ds-params-block .ds-param')
            );

            $params = [];
            if (!empty($paramsNode)) {
                $paramsNode->each(function (Crawler $subNode) use (&$params) {
                    $params[] = ExceptionHelper::handleExceptionAsString(fn () => trim($subNode->filter('span')->text()));
                });
            }

            $auctionsList[] = new AuctionOverview($name, $price, $image, $shortDesc, $currency, $link, $params);
        });

        return $auctionsList;
    }
}