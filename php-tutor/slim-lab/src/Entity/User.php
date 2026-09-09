<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    /** 登录名（latin，唯一）：演示用户的规范登录账号，与中文姓名 name 分离 */
    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $username;

    /** bcrypt 哈希（password_hash）；可空：纯演示数据可以没有密码（无法登录） */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    /** @var Collection<int, Product> */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'owner', cascade: ['persist', 'remove'])]
    private Collection $products;

    public function __construct(string $name, string $email, string $username)
    {
        $this->name = $name;
        $this->email = $email;
        $this->username = $username;
        $this->createdAt = new \DateTime();
        $this->products = new ArrayCollection();
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Product> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setOwner($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            if ($product->getOwner() === $this) {
                $product->setOwner(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('User#%d (%s <%s>)', $this->id, $this->name, $this->email);
    }
}
