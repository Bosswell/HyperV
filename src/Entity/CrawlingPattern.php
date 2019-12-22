<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CrawlingPatternRepository")
 */
class CrawlingPattern
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
     * @ORM\ManyToMany(targetEntity="App\Entity\CrawlingHistory", inversedBy="crawlingPatterns")
     */
    private $crawlingHistory;

    /**
     * @ORM\Column(type="integer")
     */
    private $urlsQuantity;

    public function __construct()
    {
        $this->crawlingHistory = new ArrayCollection();
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
     * @return Collection|CrawlingHistory[]
     */
    public function getCrawlingHistory(): Collection
    {
        return $this->crawlingHistory;
    }

    public function addCrawlingHistory(CrawlingHistory $crawlingHistory): self
    {
        if (!$this->crawlingHistory->contains($crawlingHistory)) {
            $this->crawlingHistory[] = $crawlingHistory;
        }

        return $this;
    }

    public function removeCrawlingHistory(CrawlingHistory $crawlingHistory): self
    {
        if ($this->crawlingHistory->contains($crawlingHistory)) {
            $this->crawlingHistory->removeElement($crawlingHistory);
        }

        return $this;
    }

    public function getUrlsQuantity(): ?int
    {
        return $this->urlsQuantity;
    }

    public function setUrlsQuantity(int $urlsQuantity): self
    {
        $this->urlsQuantity = $urlsQuantity;

        return $this;
    }
}
