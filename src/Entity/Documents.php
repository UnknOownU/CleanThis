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
    private ?Operation $client = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Operation $salarie = null;

    #[ORM\Column(length: 50)]
    private ?string $idoperation = null;

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

    public function getClient(): ?Operation
    {
        return $this->client;
    }

    public function setClient(?Operation $client): static
    {
        $this->client = $client;

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

    public function getSalarie(): ?Operation
    {
        return $this->salarie;
    }

    public function setSalarie(?Operation $salarie): static
    {
        $this->salarie = $salarie;

        return $this;
    }

    public function getIdoperation(): ?string
    {
        return $this->idoperation;
    }

    public function setIdoperation(string $idoperation): static
    {
        $this->idoperation = $idoperation;

        return $this;
    }
}
