<?php

namespace App\Controller\Api;

use App\Base\Controller\ApiController;
use App\Dto\Crawler\CrawlerGetLinks;
use App\Exception\ValidationException;
use App\WebCrawler\WebCrawlerFacade;
use AutoMapperPlus\Exception\UnregisteredMappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/crawler", name="api_crawler_")
 */
class CrawlerController extends ApiController
{
    /**
     * @throws ValidationException
     * @throws UnregisteredMappingException
     *
     * @Route("/get/links", name="get_links")
     */
    public function getLinks(/*CrawlerGetLinks $crawlerGetLinks,*/ WebCrawlerFacade $crawlerFacade): JsonResponse
    {
//        $this->dtoValidator->validate($crawlerGetLinks);
//
//        $user = $this->mapper->mapToObject($userRegister, new User());

        $crawlerFacade = new WebCrawlerFacade();
        $dto = new CrawlerGetLinks();
        $dto->setDomainUrl('https://greencell.global/');
        $crawlerFacade->getAllWebsiteLinks($dto);

        return new JsonResponse();
    }
}
