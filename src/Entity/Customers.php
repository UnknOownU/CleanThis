<?php

namespace App\Entity;

use App\Repository\CustomersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: CustomersRepository::class)]
#[Broadcast]
class Customers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Users $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?Users
    {
        return $this->parent;
    }

    public function setParent(?Users $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
