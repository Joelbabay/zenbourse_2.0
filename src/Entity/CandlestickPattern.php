<?php

namespace App\Entity;

use App\Repository\CandlestickPatternRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandlestickPatternRepository::class)]
class CandlestickPattern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $structure = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageH = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageNameH = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageNameB = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentH = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentB = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(string $structure): static
    {
        $this->structure = $structure;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImageH(): ?string
    {
        return $this->imageH;
    }

    public function setImageH(?string $imageH): static
    {
        $this->imageH = $imageH;
        return $this;
    }

    public function getImageB(): ?string
    {
        return $this->imageB;
    }

    public function setImageB(?string $imageB): static
    {
        $this->imageB = $imageB;
        return $this;
    }

    public function getImageNameH(): ?string
    {
        return $this->imageNameH;
    }

    public function setImageNameH(?string $imageNameH): static
    {
        $this->imageNameH = $imageNameH;
        return $this;
    }

    public function getImageNameB(): ?string
    {
        return $this->imageNameB;
    }

    public function setImageNameB(?string $imageNameB): static
    {
        $this->imageNameB = $imageNameB;
        return $this;
    }

    public function getContentH(): ?string
    {
        return $this->contentH;
    }

    public function setContentH(?string $contentH): static
    {
        $this->contentH = $contentH;
        return $this;
    }

    public function getContentB(): ?string
    {
        return $this->contentB;
    }

    public function setContentB(?string $contentB): static
    {
        $this->contentB = $contentB;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
