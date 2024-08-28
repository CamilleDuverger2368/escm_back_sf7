<?php

namespace App\Entity;

use App\Repository\ListFavoriRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ListFavoriRepository::class)]
class ListFavori
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getList",
              "getEscape",

              "routeEscape",
              "routeLists"
    ])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "listFavoris")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: "listFavoris")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getList",
              "getAlterUser",


              "routeLists",
              "routeAlterUser"
    ])]
    private ?Escape $escape = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getList",
              "getEscape",
              "getAlterUser",

              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private ?\DateTimeInterface $since = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEscape(): ?Escape
    {
        return $this->escape;
    }

    public function setEscape(?Escape $escape): static
    {
        $this->escape = $escape;

        return $this;
    }

    public function getSince(): ?\DateTimeInterface
    {
        return $this->since;
    }

    public function setSince(\DateTimeInterface $since): static
    {
        $this->since = $since;

        return $this;
    }
}
