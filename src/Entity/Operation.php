<?php

namespace App\Entity;

use App\Entity\User;
use DateTimeImmutable;
use App\Repository\OperationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OperationRepository::class)]
class Operation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;


    #[ORM\Column(length: 10)]
    private ?string $zipcode_ope = null;

    #[ORM\Column(length: 255)]
    private ?string $city_ope = null;

    #[ORM\Column(length: 255)]
    private ?string $street_ope = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    private ?User $salarie = null;

/**
*@ORM\ManyToOne(targetEntity=User::class)
*@ORM\JoinColumn(nullable=false)
 */
private $createdBy;

#[ORM\Column(nullable: true)]
private ?\DateTimeImmutable $finished_at = null;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $user): self
    {
        $this->createdBy = $user;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getZipcodeOpe(): ?string
    {
        return $this->zipcode_ope;
    }

    public function setZipcodeOpe(string $zipcode_ope): static
    {
        $this->zipcode_ope = $zipcode_ope;

        return $this;
    }

    public function getCityOpe(): ?string
    {
        return $this->city_ope;
    }

    public function setCityOpe(string $city_ope): static
    {
        $this->city_ope = $city_ope;

        return $this;
    }

    public function getStreetOpe(): ?string
    {
        return $this->street_ope;
    }

    public function setStreetOpe(string $street_ope): static
    {
        $this->street_ope = $street_ope;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getSalarie(): ?User
    {
        return $this->salarie;
    }

    public function setSalarie(?User $salarie): static
    {
        $this->salarie = $salarie;

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
    public function getCustomerFullName(): ?string
    {
        if (!$this->customer) {
            return null;
        }
    
        return $this->customer->getFirstname() . ' ' . $this->customer->getName();
    }
  
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: "created_at")]
    private ?\DateTimeImmutable $rdv_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getRdvAt(): ?\DateTimeImmutable
    {
        return $this->rdv_at;
    }

    public function setRdvAt(?\DateTimeImmutable $rdv_at): self
    {
        $this->rdv_at = $rdv_at;
        return $this;

    }
}
