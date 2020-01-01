<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DomainRepository")
 */
class Domain
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CrawlingHistory", mappedBy="domain")
     */
    private $crawlingHistory;

    public function __construct()
    {
        $this->crawlingHistory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
            $crawlingHistory->setDomain($this);
        }

        return $this;
    }

    public function removeCrawlingHistory(CrawlingHistory $crawlingHistory): self
    {
        if ($this->crawlingHistory->contains($crawlingHistory)) {
            $this->crawlingHistory->removeElement($crawlingHistory);
            // set the owning side to null (unless already changed)
            if ($crawlingHistory->getDomain() === $this) {
                $crawlingHistory->setDomain(null);
            }
        }

        return $this;
    }
}
