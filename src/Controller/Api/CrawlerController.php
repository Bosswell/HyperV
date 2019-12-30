<?php

namespace App\Controller\Api;

use App\Base\Controller\ApiController;
use App\Dto\Crawler\CrawlDomainLinksDto;
use App\Exception\ValidationException;
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
     * @throws ValidationException
     * @ParamConverter("crawlerGetLinks", converter="dto_converter", class="CrawlerGetDomainLinks")
     * @Route("/get/links", name="get_links")
     */
    public function getLinks(CrawlDomainLinksDto $crawlerGetLinksDto): JsonResponse
    {
        $this->dtoValidator->validate($crawlerGetLinksDto);

        // Using asynchronous call would be bless :(
        // RabbitMQ queue would be even better for giving async response
        // TODO Implement Messenger Component

        $exec = '> /dev/null 2>/dev/null &';
        return new JsonResponse([
            [],
            Response::HTTP_OK
        ]);
    }
}

