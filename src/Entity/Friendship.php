<?php

namespace App\Entity;

use App\Repository\FriendshipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
class Friendship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAlterUser",
              "getRequestsAndFriendships"
    ])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getAlterUser"])]
    private ?\DateTimeInterface $since = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getAlterUser",
              "getRequestsAndFriendships"
    ])]
    private ?User $sender = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getAlterUser",
              "getRequestsAndFriendships"
    ])]
    private ?User $receiver = null;

    #[ORM\Column]
    #[Groups(["getAlterUser",
            "getRequestsAndFriendships"
    ])]
    private ?bool $friend = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSince(): ?\DateTimeInterface
    {
        return $this->since;
    }

    public function setSince(?\DateTimeInterface $since): static
    {
        $this->since = $since;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(User $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function isFriend(): ?bool
    {
        return $this->friend;
    }

    public function setFriend(bool $friend): static
    {
        $this->friend = $friend;

        return $this;
    }
}
