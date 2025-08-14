<?php

namespace App\Entity;

use App\Repository\PageContentRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\StockExample;

#[ORM\Entity(repositoryClass: PageContentRepository::class)]
class PageContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\OneToOne(targetEntity: Menu::class, inversedBy: 'pageContent')]
    #[ORM\JoinColumn(nullable: true)] // Rendre la relation optionnelle
    private ?Menu $menu = null;

    #[ORM\OneToOne(inversedBy: 'pageContent', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)] // Rendre la relation optionnelle
    private ?StockExample $stockExample = null;

    // Champ virtuel pour le formulaire (non mappé en base de données)
    private ?string $section = null;

    // Champ virtuel pour le formulaire
    private ?string $contentType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;
        return $this;
    }

    public function getStockExample(): ?StockExample
    {
        return $this->stockExample;
    }

    public function setStockExample(?StockExample $stockExample): static
    {
        $this->stockExample = $stockExample;
        return $this;
    }

    // Getters et setters pour le champ virtuel section
    public function getSection(): ?string
    {
        // Si on a un menu, on retourne sa section
        if ($this->menu) {
            return $this->menu->getSection();
        }
        // Sinon on retourne la valeur du champ virtuel
        return $this->section;
    }

    public function setSection(?string $section): static
    {
        $this->section = $section;
        return $this;
    }

    public function getContentType(): ?string
    {
        if ($this->getMenu() !== null) {
            return 'menu';
        }
        if ($this->getStockExample() !== null) {
            return 'stock_example';
        }
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }
}
