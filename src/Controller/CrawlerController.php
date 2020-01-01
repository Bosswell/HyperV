<?php

namespace App\Controller;

use App\Base\Controller\ApiController;
use App\Message\Crawler\CrawlDomainLinksMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/crawler", name="crawler_")
 */
class CrawlerController extends ApiController
{
    /**
     * @ParamConverter("crawlDomainLinksMessage", converter="message_converter", class="App\Message\Crawler\CrawlDomainLinksMessage")
     * @Route("/extract", name="get_links")
     */
    public function crawlDomainLinks(CrawlDomainLinksMessage $crawlDomainLinksMessage): JsonResponse
    {
        $this->messageBus->dispatch($crawlDomainLinksMessage);

        return new JsonResponse([[
                'message' => sprintf('Domain %s has been successfully crawled', $crawlDomainLinksMessage->getDomainUrl())
            ],
            Response::HTTP_OK
        ]);
    }
}

