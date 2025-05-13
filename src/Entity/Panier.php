<?php

// src/Entity/Panier.php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'json')]
    private array $items = [];

    // ... (vos autres méthodes restent identiques)
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }
    public function getTotalItems(): int
    {
        return array_sum($this->items);
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    // Si vous voulez garder la méthode addItem telle quelle
    public function addItem(int $livreId, int $quantity): void
    {
        $this->items[$livreId] = ($this->items[$livreId] ?? 0) + $quantity;
    }
    public function getItems(): array
{
    return $this->items;
}

public function setItems(array $items): self
{
    $this->items = $items;
    return $this;
}
}