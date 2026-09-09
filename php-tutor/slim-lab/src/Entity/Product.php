<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function __construct(string $name, string $price, ?User $owner = null)
    {
        $this->name = $name;
        $this->price = $price;
        $this->owner = $owner;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('Product#%d (%s ¥%s)', $this->id, $this->name, $this->price);
    }
}
