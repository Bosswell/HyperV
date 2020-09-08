<?php

namespace App\Command;

use App\Message\Crawler\FilterCrawledLinksMessage;
use App\WebCrawler\Utils\Selector;
use App\WebCrawler\Utils\SelectorCollection;
use App\WebCrawler\WebCrawlerFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Wa72\HtmlPageDom\HtmlPage;


class WebCrawlerExtractCommand extends Command
{
    private WebCrawlerFacade $crawlerFacade;

    public function __construct(WebCrawlerFacade $crawlerFacade)
    {
        parent::__construct('crawler:extract:link');

        $this->crawlerFacade = $crawlerFacade;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = new SelectorCollection();
        $selector1 = new Selector('desc', '//*[@id="opis_produktuc"]', Selector::XPATH_TYPE);
        $selector2 = new Selector('table', '//html/body/div/div[3]/div/div[1]/div/div/div/div[2]/div/div[2]/div/table', Selector::XPATH_TYPE);
        $selector3 = new Selector('shortDesc', '//html/body/div/div[3]/div/div[1]/div/div/div/div[1]/div/div[2]/div[2]/div[1]', Selector::XPATH_TYPE);
        $selector4 = new Selector('image', '//html/body/div/div[3]/div/div[1]/div/div/div/div[1]/div/div[1]/div/div/a/img', Selector::XPATH_TYPE);
        $selector5 = new Selector('reference', '//html/body/div/div[3]/div/div[1]/div/div/div/div[1]/div/div[2]/h1', Selector::XPATH_TYPE);
        $collection->add($selector1);
        $collection->add($selector2);
        $collection->add($selector3);
        $collection->add($selector4);
        $collection->add($selector5);
//        $this->crawlerFacade->extractSelectorsFromWebPage($collection, 'https://sevrapolska.pl/produkt/sevra-sev-12cao');
        $this->crawlerFacade->extractSelectorsFromWebPage($collection, 'https://sevrapolska.pl/produkt/sevra-ecomi-sev-09fv');

        $desc = $selector1->getOuterHtml();
        $shortDesc = $selector3->getOuterHtml();
        $table = '<div class="row"><div class="col-lg-12"><div class="responsive-table">'. $selector2->getOuterHtml() .'</div></div></div>';
        $images = [];

        $crawler = new Crawler($desc, 'https://sevrapolska.pl');
        foreach ($crawler->filter('img')->images() as $image) {
            $images[] = $image->getUri();
        }

        $shortDesc = str_replace('col-md-4', 'col-md-4 col-12', $shortDesc);
        $desc = str_replace('col-md-6', 'col-md-6 col-12', $desc);
        $desc = str_replace('/uploads/filemanager', '/images/uploads', $desc);

        $page = new HtmlPage($desc);
        $page->filter('.row')->eq(3)->append($shortDesc);
        $page->filter('.row')->last()->append($table);

        $desc = $page->filter('#opis_produktuc')->saveHTML();

        $file = new \SplFileObject('file.csv', 'w+');
        $file->setCsvControl(';', '\'');
        $file->fputcsv([
            'sku',
            'description',
            'images'
        ]);
        $file->fputcsv([
            $selector5->getValue(),
            $desc,
            implode(',', $images)
        ]);
//        fwrite($file, implode(',', $data) . PHP_EOL);
//        $file->fwrite(implode(',', [
//            $selector5->getValue(),
//            $desc,
//            implode(';', $images)
//        ]) . PHP_EOL);

        return 0;
    }
}
