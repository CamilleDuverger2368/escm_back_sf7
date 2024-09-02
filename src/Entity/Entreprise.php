<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["finder"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"entreprise's name can't be blanck")]
    #[Groups(["getEscape",
              "finder",

              "routeEscape"
    ])]
    private ?string $name = null;

    /**
     * @var Collection<int, City> $cities entreprise's localisation
     */
    #[ORM\ManyToMany(targetEntity: City::class)]
    private Collection $cities;

    /**
     * @var Collection<int, Escape> $escapes entreprise's escapes
     */
    #[ORM\ManyToMany(targetEntity: Escape::class, mappedBy: "entreprises")]
    private Collection $escapes;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->escapes = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        $this->cities->removeElement($city);

        return $this;
    }

    public function clearCities(): self
    {
        foreach ($this->cities as $city) {
            $this->cities->removeElement($city);
        }

        return $this;
    }

    /**
     * @return Collection<int, Escape>
     */
    public function getEscapes(): Collection
    {
        return $this->escapes;
    }

    public function addEscape(Escape $escape): static
    {
        if (!$this->escapes->contains($escape)) {
            $this->escapes->add($escape);
        }

        return $this;
    }

    public function removeEscape(Escape $escape): static
    {
        $this->escapes->removeElement($escape);

        return $this;
    }

    public function clearEscapes(): self
    {
        foreach ($this->escapes as $escape) {
            $this->escapes->removeElement($escape);
        }

        return $this;
    }
}
