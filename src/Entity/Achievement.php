<?php

namespace App\Entity;

use App\Repository\AchievementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchievementRepository::class)]
class Achievement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $conditionType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $tropheeType = null;

    #[ORM\Column(length: 255)]
    private ?string $trophee = null;

    #[ORM\Column]
    private ?bool $scalable = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?self $previousStep = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?self $nextStep = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'achievements')]
    private Collection $users;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $checker = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getConditionType(): ?string
    {
        return $this->conditionType;
    }

    public function setConditionType(string $conditionType): static
    {
        $this->conditionType = $conditionType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTropheeType(): ?string
    {
        return $this->tropheeType;
    }

    public function setTropheeType(string $tropheeType): static
    {
        $this->tropheeType = $tropheeType;

        return $this;
    }

    public function getTrophee(): ?string
    {
        return $this->trophee;
    }

    public function setTrophee(string $trophee): static
    {
        $this->trophee = $trophee;

        return $this;
    }

    public function isScalable(): ?bool
    {
        return $this->scalable;
    }

    public function setScalable(bool $scalable): static
    {
        $this->scalable = $scalable;

        return $this;
    }

    public function getPreviousStep(): ?self
    {
        return $this->previousStep;
    }

    public function setPreviousStep(?self $previousStep): static
    {
        $this->previousStep = $previousStep;

        return $this;
    }

    public function getNextStep(): ?self
    {
        return $this->nextStep;
    }

    public function setNextStep(?self $nextStep): static
    {
        $this->nextStep = $nextStep;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

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

    public function getChecker(): ?string
    {
        return $this->checker;
    }

    public function setChecker(string $checker): static
    {
        $this->checker = $checker;

        return $this;
    }
}
