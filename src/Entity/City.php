<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"city's name can't be blanck")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "city's name must have between 1 and 255 characters",
        maxMessage: "city's name must have between 1 and 255 characters"
    )]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "city's name cannot contain a number",)]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getCurrent", "getEscape", "getAlterUser"])]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
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
}
