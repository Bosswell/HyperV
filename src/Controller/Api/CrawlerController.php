<?php

namespace App\Controller\Api;

use App\Base\Controller\ApiController;
use App\Dto\Crawler\CrawlerGetLinks;
use App\Exception\ValidationException;
use App\PageExtractor\ExtractorException;
use App\PageExtractor\LinkExtractorFacade;
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
     * @throws ExtractorException
     * @throws ValidationException
     *
     * @ParamConverter("crawlerGetLinks", converter="dto_converter", class="App\Dto\Crawler\CrawlerGetLinks")
     * @Route("/get/links", name="get_links")
     */
    public function getLinks(CrawlerGetLinks $crawlerGetLinks, LinkExtractorFacade $linkExtractorFacade): JsonResponse
    {
        ini_set('memory_limit', '1048M');
        $this->dtoValidator->validate($crawlerGetLinks);
        $links = $linkExtractorFacade->getLinks($crawlerGetLinks);

        return new JsonResponse([
            $links,
            Response::HTTP_OK
        ]);
    }
}

