<?php

namespace App\Entity;

use App\Repository\EmployesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: EmployesRepository::class)]
#[Broadcast]
class Employes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'employes')]
    private ?Users $parent = null;

    #[ORM\Column]
    private ?int $countope = null;

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

    public function getCountope(): ?int
    {
        return $this->countope;
    }

    public function setCountope(int $countope): static
    {
        $this->countope = $countope;

        return $this;
    }
}
