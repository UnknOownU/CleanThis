<?php

namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OperationRepository::class)]
class Operation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $rdv_at = null;

    #[ORM\Column(length: 20)]
    private ?string $zipcode_ope = null;

    #[ORM\Column(length: 50)]
    private ?string $city_ope = null;

    #[ORM\Column(length: 50)]
    private ?string $street_ope = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    private ?User $salarie = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    private ?User $customer = null;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
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

    public function getSalarie(): ?User
    {
        return $this->salarie;
    }

    public function setSalarie(?User $salarie): static
    {
        $this->salarie = $salarie;

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
