<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="CrawledDomainRepository")
 */
class CrawledDomain
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
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $domainName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CrawledDomainPattern", mappedBy="crawledDomains")
     */
    private $crawledDomainPatterns;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;


    public function __construct()
    {
        $this->createdAt = new DateTime('now');
        $this->crawledDomainPatterns = new ArrayCollection();
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


    public function getDomainName(): ?string
    {
        return $this->domainName;
    }

    public function setDomainName(string $domainName): self
    {
        $this->domainName = $domainName;

        return $this;
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
     * @return Collection|CrawledDomainPattern[]
     */
    public function getCrawledDomainPatterns(): Collection
    {
        return $this->crawledDomainPatterns;
    }

    public function addCrawledDomainPattern(CrawledDomainPattern $crawledDomainPattern): self
    {
        if (!$this->crawledDomainPatterns->contains($crawledDomainPattern)) {
            $this->crawledDomainPatterns[] = $crawledDomainPattern;
            $crawledDomainPattern->addCrawledDomain($this);
        }

        return $this;
    }

    public function removeCrawledDomainPattern(CrawledDomainPattern $crawledDomainPattern): self
    {
        if ($this->crawledDomainPatterns->contains($crawledDomainPattern)) {
            $this->crawledDomainPatterns->removeElement($crawledDomainPattern);
            $crawledDomainPattern->removeCrawledDomain($this);
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
}
