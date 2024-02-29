<?php

namespace App\Entity;

use App\Repository\DocumentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentsRepository::class)]
class Documents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Operations $customer = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Operations $salarie = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Operations $operation = null;

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

    public function getCustomer(): ?Operations
    {
        return $this->customer;
    }

    public function setCustomer(?Operations $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getSalarie(): ?Operations
    {
        return $this->salarie;
    }

    public function setSalarie(?Operations $salarie): static
    {
        $this->salarie = $salarie;

        return $this;
    }

    public function getOperation(): ?Operations
    {
        return $this->operation;
    }

    public function setOperation(?Operations $operation): static
    {
        $this->operation = $operation;

        return $this;
    }
}
