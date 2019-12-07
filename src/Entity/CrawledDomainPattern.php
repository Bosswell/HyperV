<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CrawledDomainPatternRepository")
 */
class CrawledDomainPattern
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pattern;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CrawledDomain", inversedBy="crawledDomainPatterns")
     */
    private $crawledDomains;

    public function __construct()
    {
        $this->crawledDomains = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return Collection|CrawledDomain[]
     */
    public function getCrawledDomains(): Collection
    {
        return $this->crawledDomains;
    }

    public function addCrawledDomain(CrawledDomain $crawledDomain): self
    {
        if (!$this->crawledDomains->contains($crawledDomain)) {
            $this->crawledDomains[] = $crawledDomain;
        }

        return $this;
    }

    public function removeCrawledDomain(CrawledDomain $crawledDomain): self
    {
        if ($this->crawledDomains->contains($crawledDomain)) {
            $this->crawledDomains->removeElement($crawledDomain);
        }

        return $this;
    }
}
