<?php

namespace App\Controller\Api;

use App\Base\Controller\ApiController;
use App\Dto\Crawler\CrawlerGetLinks;
use App\Exception\ValidationException;
use App\PageExtractor\LinkExtractorFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WebCrawler\WebCrawlerException;

/**
 * @Route("/api/crawler", name="api_crawler_")
 */
class CrawlerController extends ApiController
{
    /**
     * @throws ValidationException
     * @throws WebCrawlerException
     *
     * @ParamConverter("crawlerGetLinks", converter="dto_converter", class="App\Dto\Crawler\CrawlerGetLinks")
     * @Route("/get/links", name="get_links")
     */
    public function getLinks(CrawlerGetLinks $crawlerGetLinks, LinkExtractorFacade $linkExtractorFacade): JsonResponse
    {
        $this->dtoValidator->validate($crawlerGetLinks);
        $links = $linkExtractorFacade->getLinks($crawlerGetLinks);

        return new JsonResponse([
            count($links),
            Response::HTTP_OK
        ]);
    }
}
