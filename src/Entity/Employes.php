<?php

namespace App\Entity;

use App\Repository\EmployesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(targetEntity: Operations::class, mappedBy: 'employe')]
    private Collection $idemploye;

    public function __construct()
    {
        $this->idemploye = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Operations>
     */
    public function getIdemploye(): Collection
    {
        return $this->idemploye;
    }

    public function addIdemploye(Operations $idemploye): static
    {
        if (!$this->idemploye->contains($idemploye)) {
            $this->idemploye->add($idemploye);
            $idemploye->setEmploye($this);
        }

        return $this;
    }

    public function removeIdemploye(Operations $idemploye): static
    {
        if ($this->idemploye->removeElement($idemploye)) {
            // set the owning side to null (unless already changed)
            if ($idemploye->getEmploye() === $this) {
                $idemploye->setEmploye(null);
            }
        }

        return $this;
    }
}
