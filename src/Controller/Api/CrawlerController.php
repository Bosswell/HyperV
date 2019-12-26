<?php

namespace App\Controller\Api;

use App\Base\Controller\ApiController;
use App\Dto\Crawler\CrawlDomainLinksDto;
use App\WebCrawler\WebCrawlerFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/crawler", name="api_crawler_")
 */
class CrawlerController extends ApiController
{
    /**
     *
     * @ParamConverter("crawlerGetLinks", converter="dto_converter", class="CrawlerGetDomainLinks")
     * @Route("/get/links", name="get_links")
     */
    public function getLinks(CrawlDomainLinksDto $crawlerGetLinks, WebCrawlerFacade $webCrawlerFacade): JsonResponse
    {;

        return new JsonResponse([
            [],
            Response::HTTP_OK
        ]);
    }
}

