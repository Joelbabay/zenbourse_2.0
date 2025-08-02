<?php

namespace App\Entity;

use App\Repository\PageContentRepository;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\OneToOne(inversedBy: 'pageContent', cascade: ['persist'], orphanRemoval: false)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    // Champ virtuel pour le formulaire (non mappé en base de données)
    private ?string $section = null;

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

    public function setMenu(Menu $menu): static
    {
        $this->menu = $menu;
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
}
