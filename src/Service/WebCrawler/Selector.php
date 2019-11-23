<?php

namespace App\Service\WebCrawler;

use InvalidArgumentException;

class Selector
{
    const XPATH_TYPE = 'XPath';
    const CSS_TYPE = 'CSSSelector';

    const AVAILABLE_TYPES = [
        self::XPATH_TYPE,
        self::CSS_TYPE,
    ];

    /** @var string */
    private $name;

    /** @var string */
    private $path;

    /** @var string */
    private $value;

    /** @var string */
    private $type;

    public function __construct(string $name, string $path, string $type)
    {
        $this->name = $name;
        $this->path = $path;

        if (false === in_array($type, self::AVAILABLE_TYPES)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Type [%s] is not allowed for Selector. Available types [%s]',
                    $type,
                    implode(', ', self::AVAILABLE_TYPES)
                )
            );
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = trim($value);
    }
}