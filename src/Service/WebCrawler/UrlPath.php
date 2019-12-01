<?php

namespace App\Service\WebCrawler;

use InvalidArgumentException;


class UrlPath
{
    /** @var string */
    private $url;

    /** @var string */
    private $domain = '';

    /** @var bool */
    private $isRelative = true;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $url)
    {
        $this->url = strtok($url, '#');

        $this->parseUrl();
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isRelative(): bool
    {
        return $this->isRelative;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return
            filter_var($this->url, FILTER_VALIDATE_URL)
            && (bool)!preg_match('/\.(jpg|png|gif|jpeg|pdf)/', $this->url);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->url;
    }

    /**
     * Parse URL to get domain
     *
     * @throws InvalidArgumentException
     */
    private function parseUrl(): void
    {
        if (!$this->isValid()) {
            return;
        }

        // Remove everything after question mark
        $url = strtok($this->url, '?');

        // Remove .www
        $url = preg_replace('/www\./', '', $url);

        // If url does not contain http/s word, we assume that url is relative
        if (preg_match('/https?/', $url)) {
            if ($url[-1] !== '/') {
                $url .= '/';
            }

            if (!preg_match('/\/\/.*?\//', $url, $matches)) {
                throw new InvalidArgumentException(sprintf('Given URL is not valid [%s]', $this->url));
            }

            $this->domain = trim($matches[0], '/');
            $this->isRelative = false;
        }
    }
}

