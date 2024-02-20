<?php

namespace App\Entity;

use App\Repository\OperationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: OperationsRepository::class)]
#[Broadcast]
class Operations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Type = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?int $Forfait = null;

    #[ORM\Column]
    private ?int $Status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $rdv_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finished_at = null;

    #[ORM\Column(length: 255)]
    private ?string $zipCodeOpe = null;

    #[ORM\Column(length: 255)]
    private ?string $cityOpe = null;

    #[ORM\Column(length: 255)]
    private ?string $streetOpe = null;

    #[ORM\ManyToOne(inversedBy: 'idcustomer')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer = null;

    #[ORM\ManyToOne(inversedBy: 'idemploye')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employes $employe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getForfait(): ?int
    {
        return $this->Forfait;
    }

    public function setForfait(int $Forfait): static
    {
        $this->Forfait = $Forfait;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->Status;
    }

    public function setStatus(int $Status): static
    {
        $this->Status = $Status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getRdvAt(): ?\DateTimeImmutable
    {
        return $this->rdv_at;
    }

    public function setRdvAt(?\DateTimeImmutable $rdv_at): static
    {
        $this->rdv_at = $rdv_at;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?\DateTimeImmutable $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    public function getZipCodeOpe(): ?string
    {
        return $this->zipCodeOpe;
    }

    public function setZipCodeOpe(string $zipCodeOpe): static
    {
        $this->zipCodeOpe = $zipCodeOpe;

        return $this;
    }

    public function getCityOpe(): ?string
    {
        return $this->cityOpe;
    }

    public function setCityOpe(string $cityOpe): static
    {
        $this->cityOpe = $cityOpe;

        return $this;
    }

    public function getStreetOpe(): ?string
    {
        return $this->streetOpe;
    }

    public function setStreetOpe(string $streetOpe): static
    {
        $this->streetOpe = $streetOpe;

        return $this;
    }

    public function getCustomer(): ?Customers
    {
        return $this->customer;
    }

    public function setCustomer(?Customers $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getEmploye(): ?Employes
    {
        return $this->employe;
    }

    public function setEmploye(?Employes $employe): static
    {
        $this->employe = $employe;

        return $this;
    }
}
