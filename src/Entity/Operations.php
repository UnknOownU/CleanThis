<?php

namespace App\Entity;

use App\Repository\OperationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OperationsRepository::class)]
class Operations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $rdv_at = null;

    #[ORM\Column(length: 255)]
    private ?string $street_op = null;

    #[ORM\Column(length: 5)]
    private ?string $zipcode_op = null;

    #[ORM\Column(length: 50)]
    private ?string $city_op = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    private ?Users $customer = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    private ?users $salarie = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $finished_at = null;

    #[ORM\OneToMany(targetEntity: Documents::class, mappedBy: 'customer')]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
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

    public function setRdvAt(\DateTimeImmutable $rdv_at): static
    {
        $this->rdv_at = $rdv_at;

        return $this;
    }

    public function getStreetOp(): ?string
    {
        return $this->street_op;
    }

    public function setStreetOp(string $street_op): static
    {
        $this->street_op = $street_op;

        return $this;
    }

    public function getZipcodeOp(): ?string
    {
        return $this->zipcode_op;
    }

    public function setZipcodeOp(string $zipcode_op): static
    {
        $this->zipcode_op = $zipcode_op;

        return $this;
    }

    public function getCityOp(): ?string
    {
        return $this->city_op;
    }

    public function setCityOp(string $city_op): static
    {
        $this->city_op = $city_op;

        return $this;
    }

    public function getCustomer(): ?Users
    {
        return $this->customer;
    }

    public function setCustomer(?Users $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getSalarie(): ?users
    {
        return $this->salarie;
    }

    public function setSalarie(?users $salarie): static
    {
        $this->salarie = $salarie;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finished_at;
    }

    public function setFinishedAt(\DateTimeImmutable $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    /**
     * @return Collection<int, Documents>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Documents $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCustomer($this);
        }

        return $this;
    }

    public function removeDocument(Documents $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCustomer() === $this) {
                $document->setCustomer(null);
            }
        }

        return $this;
    }
}
