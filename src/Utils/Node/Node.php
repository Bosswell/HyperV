<?php

namespace App\Utils\Node;

class Node
{
    /** @var string */
    private $name;

    /** @var NodeCollection */
    private $filters;

    /** @var NodeCollection */
    private $siblings;

    /** @var Node */
    private $parent;

    public function __construct(string $querySelector)
    {
        $this->name = $querySelector;

        $this->filters = new NodeCollection();
        $this->siblings = new NodeCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function parent(): ?Node
    {
        return $this->parent;
    }

    public function addFilter(NodeFilter $filter): void
    {
        $this->filters->add($filter->name, $filter);
    }

    public function getFilters(): NodeCollection
    {
        return $this->filters;
    }

    public function addSibling(Node $sibling): void
    {
        $this->siblings->add($sibling->name, $sibling);
    }

    public function getSiblings(): NodeCollection
    {
        return $this->siblings;
    }
}