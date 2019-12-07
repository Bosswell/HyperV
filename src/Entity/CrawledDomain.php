<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass="CrawledDomainHistoryRepository")
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
     * @ORM\Column(type="integer")
     */
    private $crawledUrls;

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

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
        $this->crawledDomainPatterns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCrawledUrls(): ?int
    {
        return $this->crawledUrls;
    }

    public function setCrawledUrls(int $crawledUrls): self
    {
        $this->crawledUrls = $crawledUrls;

        return $this;
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
}
