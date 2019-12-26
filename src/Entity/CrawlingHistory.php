<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CrawlingHistoryRepository")
 */
class CrawlingHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $crawledLinks;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $extractedLinks;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CrawlingPattern", mappedBy="crawlingHistory")
     */
    private $crawlingPatterns;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Domain", inversedBy="crawlingHistory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $domain;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;


    public function __construct()
    {
        $this->createdAt = $this->updatedAt = new DateTime('now');
        $this->crawlingPatterns = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getCrawledLinks(): int
    {
        return $this->crawledLinks;
    }

    /**
     * @return $this
     * @param int $crawledLinks
     */
    public function setCrawledLinks(int $crawledLinks): self
    {
        $this->crawledLinks = $crawledLinks;

        return $this;
    }

    /**
     * @return int
     */
    public function getExtractedLinks(): int
    {
        return $this->extractedLinks;
    }

    /**
     * @return $this
     * @param int $extractedLinks
     */
    public function setExtractedLinks(int $extractedLinks): self
    {
        $this->extractedLinks = $extractedLinks;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|CrawlingPattern[]
     */
    public function getCrawledDomainPatterns(): Collection
    {
        return $this->crawlingPatterns;
    }

    public function addCrawlingPattern(CrawlingPattern $crawlingPattern): self
    {
        if (!$this->crawlingPatterns->contains($crawlingPattern)) {
            $this->$crawlingPattern[] = $crawlingPattern;
            $crawlingPattern->addCrawlingHistory($this);
        }

        return $this;
    }

    public function removeCrawlingPattern(CrawlingPattern $crawlingPattern): self
    {
        if ($this->crawlingPatterns->contains($crawlingPattern)) {
            $this->crawlingPatterns->removeElement($crawlingPattern);
            $crawlingPattern->removeCrawlingHistory($this);
        }

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getDomain(): ?Domain
    {
        return $this->domain;
    }

    public function setDomain(?Domain $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
