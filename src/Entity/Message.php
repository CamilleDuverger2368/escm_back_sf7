<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getRoom", "getMessages"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getRoom", "getMessages"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getRoom", "getMessages"])]
    private ?User $sender = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getRoom", "getMessages"])]
    private ?string $message = null;

    /**
     * @var Collection<int, User> users who read message
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[Groups(["getRoom"])]
    private Collection $readBy;

    #[ORM\ManyToOne(inversedBy: "messages")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getMessages"])]
    private ?Room $room = null;

    public function __construct()
    {
        $this->readBy = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getReadBy(): Collection
    {
        return $this->readBy;
    }

    public function addReadBy(User $readBy): static
    {
        if (!$this->readBy->contains($readBy)) {
            $this->readBy->add($readBy);
        }

        return $this;
    }

    public function removeReadBy(User $readBy): static
    {
        $this->readBy->removeElement($readBy);

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }
}
