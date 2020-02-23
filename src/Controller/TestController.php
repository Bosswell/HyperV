<?php

namespace App\Controller;

use App\Message\Crawler\AuctionDetails\OtomotoDetails;
use App\Message\Crawler\CrawlAuctionsOverviewsMessage;
use App\Service\MessageValidator;
use App\VehicleWebsite\Otomoto\OtomotoClient;
use App\VehicleWebsite\Otomoto\OtomotoProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function index(OtomotoProvider $otomotoProvider, MessageValidator $messageValidator)
    {
        $message = new CrawlAuctionsOverviewsMessage(['type' => 'cars', 'searchQuery' => 'skoda', 'pageNumber' => 10]);
        $message->setOtomotoDetails(new OtomotoDetails());
        $messageValidator->validate($message);
        dump($message);
        $auctions = $otomotoProvider->getAuctionsOverviews($message);
        dump($auctions);

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
