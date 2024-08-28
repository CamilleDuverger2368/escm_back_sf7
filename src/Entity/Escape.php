<?php

namespace App\Entity;

use App\Repository\EscapeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EscapeRepository::class)]
class Escape
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEscape",
              "getList",
              "finder",
              "getSessions",


              "routeEscape",
              "routeLists"
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "name can't be blanck")]
    #[Groups(["getEscape",
              "getList",
              "finder",
              "getAlterUser",
              "getSessions",


              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\Type(type: "integer", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getEscape",
              "finder",


              "routeEscape"
    ])]
    private ?int $time = null;

    #[ORM\Column]
    #[Assert\Type(type: "integer", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getEscape",
              "finder",


              "routeEscape"
    ])]
    private ?int $minPlayer = null;

    #[ORM\Column]
    #[Assert\Type(type: "integer", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getEscape",
              "finder",


              "routeEscape"
    ])]
    private ?int $maxPlayer = null;

    #[ORM\Column]
    #[Assert\Type(type: "integer", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private ?int $level = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: "integer", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private ?int $price = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: "integer", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private ?int $age = null;

    /**
     * @var Collection<int, Entreprise> $entreprises escape's entreprises
     */
    #[ORM\ManyToMany(targetEntity: Entreprise::class, inversedBy: "escapes")]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private Collection $entreprises;

    /**
     * @var Collection<int, City> $cities escape's localisation
     */
    #[ORM\ManyToMany(targetEntity: City::class)]
    private Collection $cities;

    /**
     * @var Collection<int, Description> $descriptions escape's descriptions
     */
    #[ORM\OneToMany(mappedBy: "escape", targetEntity: Description::class, orphanRemoval: true)]
    private Collection $descriptions;

    /**
     * @var Collection<int, Link> $links escape's links
     */
    #[ORM\OneToMany(mappedBy: "escape", targetEntity: Link::class, orphanRemoval: true)]
    private Collection $links;

    /**
     * @var Collection<int, Tag> $tags escape's tags
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private Collection $tags;

    /**
     * @var Collection<int, Grade> $grades escape's grades
     */
    #[ORM\OneToMany(mappedBy: "escape", targetEntity: Grade::class, orphanRemoval: true)]
    private Collection $grades;

    /**
     * @var Collection<int, ListFavori> $listFavoris escape's favoris
     */
    #[ORM\OneToMany(mappedBy: "escape", targetEntity: ListFavori::class)]
    #[Groups(["getEscape",


              "routeEscape"
    ])]
    private Collection $listFavoris;

    /**
     * @var Collection<int, ListToDo> $listToDos escape's to-dos
     */
    #[ORM\OneToMany(mappedBy: "escape", targetEntity: ListToDo::class)]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private Collection $listToDos;

    #[ORM\Column]
    #[Groups(["getEscape",

              "routeEscape"
    ])]
    private ?bool $actual = null;

    public function __construct()
    {
        $this->entreprises = new ArrayCollection();
        $this->cities = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->links = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->grades = new ArrayCollection();
        $this->listFavoris = new ArrayCollection();
        $this->listToDos = new ArrayCollection();
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

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(int $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getMinPlayer(): ?int
    {
        return $this->minPlayer;
    }

    public function setMinPlayer(int $minPlayer): static
    {
        $this->minPlayer = $minPlayer;

        return $this;
    }

    public function getMaxPlayer(): ?int
    {
        return $this->maxPlayer;
    }

    public function setMaxPlayer(int $maxPlayer): static
    {
        $this->maxPlayer = $maxPlayer;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return Collection<int, Entreprise>
     */
    public function getEntreprises(): Collection
    {
        return $this->entreprises;
    }

    public function addEntreprise(Entreprise $entreprise): static
    {
        if (!$this->entreprises->contains($entreprise)) {
            $this->entreprises->add($entreprise);
        }

        return $this;
    }

    public function removeEntreprise(Entreprise $entreprise): static
    {
        $this->entreprises->removeElement($entreprise);

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

    /**
     * @return Collection<int, Description>
     */
    public function getDescriptions(): Collection
    {
        return $this->descriptions;
    }

    public function addDescription(Description $description): static
    {
        if (!$this->descriptions->contains($description)) {
            $this->descriptions->add($description);
            $description->setEscape($this);
        }

        return $this;
    }

    public function removeDescription(Description $description): static
    {
        if ($this->descriptions->removeElement($description)) {
            // set the owning side to null (unless already changed)
            if ($description->getEscape() === $this) {
                $description->setEscape(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setEscape($this);
        }

        return $this;
    }

    public function removeLink(Link $link): static
    {
        if ($this->links->removeElement($link)) {
            // set the owning side to null (unless already changed)
            if ($link->getEscape() === $this) {
                $link->setEscape(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Grade>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setEscape($this);
        }

        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        if ($this->grades->removeElement($grade)) {
            // set the owning side to null (unless already changed)
            if ($grade->getEscape() === $this) {
                $grade->setEscape(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListFavori>
     */
    public function getListFavoris(): Collection
    {
        return $this->listFavoris;
    }

    public function addListFavori(ListFavori $listFavori): static
    {
        if (!$this->listFavoris->contains($listFavori)) {
            $this->listFavoris->add($listFavori);
            $listFavori->setEscape($this);
        }

        return $this;
    }

    public function removeListFavori(ListFavori $listFavori): static
    {
        if ($this->listFavoris->removeElement($listFavori)) {
            // set the owning side to null (unless already changed)
            if ($listFavori->getEscape() === $this) {
                $listFavori->setEscape(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListToDo>
     */
    public function getListToDos(): Collection
    {
        return $this->listToDos;
    }

    public function addListToDo(ListToDo $listToDo): static
    {
        if (!$this->listToDos->contains($listToDo)) {
            $this->listToDos->add($listToDo);
            $listToDo->setEscape($this);
        }

        return $this;
    }

    public function removeListToDo(ListToDo $listToDo): static
    {
        if ($this->listToDos->removeElement($listToDo)) {
            // set the owning side to null (unless already changed)
            if ($listToDo->getEscape() === $this) {
                $listToDo->setEscape(null);
            }
        }

        return $this;
    }

    public function isActual(): ?bool
    {
        return $this->actual;
    }

    public function setActual(bool $actual): static
    {
        $this->actual = $actual;

        return $this;
    }
}
