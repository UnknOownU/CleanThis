<?php

namespace App\Entity;

use App\Repository\CustomersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(targetEntity: Operations::class, mappedBy: 'customer')]
    private Collection $idcustomer;

    public function __construct()
    {
        $this->idcustomer = new ArrayCollection();
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

    /**
     * @return Collection<int, Operations>
     */
    public function getIdcustomer(): Collection
    {
        return $this->idcustomer;
    }

    public function addIdcustomer(Operations $idcustomer): static
    {
        if (!$this->idcustomer->contains($idcustomer)) {
            $this->idcustomer->add($idcustomer);
            $idcustomer->setCustomer($this);
        }

        return $this;
    }

    public function removeIdcustomer(Operations $idcustomer): static
    {
        if ($this->idcustomer->removeElement($idcustomer)) {
            // set the owning side to null (unless already changed)
            if ($idcustomer->getCustomer() === $this) {
                $idcustomer->setCustomer(null);
            }
        }

        return $this;
    }
}
