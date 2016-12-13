<?php

namespace NikNik\services;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class FbScraper
 * @package NikNik\services
 */
class FbRequestResult
{
    /**
     * @var Crawler
     */
    public $crawler;

    /**
     * @var object
     */
    public $response;

    /**
     * FbRequestResult constructor.
     * @param Crawler $crawler
     * @param object $response
     */
    public function __construct(Crawler $crawler, $response)
    {
        $this->crawler = $crawler;
        $this->response = $response;
    }


}
