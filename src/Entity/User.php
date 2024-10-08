<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCurrent",
              "getEscape",
              "getAlterUser",
              "getRoom",
              "getMessages",
              "getListUsers",
              "findFriends",
              "getRequestsAndFriendships",
              "getSessions",

              "getInformationsCurrentUser",
              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "email can't be blanck")]
    #[Assert\Email(message: "give us a valid email")]
    #[Groups(["getCurrent",
              "getAlterUser",
              "getRoom",
              "getListUsers",
              "getMessages",
              "getRequestsAndFriendships",
              "getSessions",

              "getInformationsCurrentUser",
              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private string $email = '';

    /**
     * @var array<string> user's roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "password can't be blanck")]
    #[Assert\Regex(
        pattern: "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/",
        message: "one UPPERCASE, one lowercase, one digit, one special character [#?!@$%^&*-], minimum of 8 characters."
    )]
    private string $password = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "name can't be blanck")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "your name must have between 1 and 255 characters",
        maxMessage: "your name must have between 1 and 255 characters"
    )]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "Your name cannot contain a number")]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getCurrent",
              "getEscape",
              "getAlterUser",
              "getRoom",
              "getMessages",
              "getListUsers",
              "findFriends",
              "getRequestsAndFriendships",
              "getSessions",


              "getInformationsCurrentUser",
              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private string $name = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "firstname can't be blanck")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "your firstname must have between 1 and 255 characters",
        maxMessage: "your firstname must have between 1 and 255 characters"
    )]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "your firstname cannot contain a number")]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getCurrent",
              "getEscape",
              "getAlterUser",
              "getRoom",
              "getMessages",
              "getListUsers",
              "findFriends",
              "getRequestsAndFriendships",
              "getSessions",



              "getInformationsCurrentUser",
              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private string $firstname = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getCurrent",
              "getEscape",
              "getAlterUser",
              "getRoom",
              "getMessages",
              "getListUsers",
              "findFriends",
              "getRequestsAndFriendships",
              "getSessions",


              "getInformationsCurrentUser",
              "routeEscape",
              "routeLists",
              "routeAlterUser"
    ])]
    private ?string $pseudo = null;

    #[ORM\Column(type: "float", nullable: true)]
    #[Assert\Type(type: "float", message: "{{ value }} is not a valid {{ type }}")]
    #[Groups(["getCurrent",
              "getAlterUser",


              "getInformationsCurrentUser",
              "routeAlterUser"
    ])]
    private ?float $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "Pronouns cannot contain a number")]
    #[Groups(["getCurrent",
              "getAlterUser",
              "getRoom",
              "getMessages",


              "getInformationsCurrentUser",
              "routeAlterUser"
    ])]
    private ?string $pronouns = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Type(type: "string", message: "{{ value }} is not a valid {{ type }}")]
    #[Assert\Regex(pattern: "/\d/", match: false, message: "Profil cannot contain a number")]
    #[Groups(["getCurrent",
              "getAlterUser",


              "getInformationsCurrentUser",
              "getAvatar",
              "routeAchievements",
              "routeAlterUser"
    ])]
    private ?string $profil = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\Type(City::class)]
    #[Groups(["getCurrent",
              "getAlterUser",


              "getInformationsCurrentUser",
              "routeAlterUser"
    ])]
    private ?City $city = null;

    #[ORM\Column]
    private ?bool $validated = null;

    #[ORM\Column(length: 300)]
    private string $link = '';

    /**
     * @var Collection<int, ListFavori> user's list favori
     */
    #[ORM\OneToMany(mappedBy: "user", targetEntity: ListFavori::class, orphanRemoval: true)]
    #[Groups(["getAlterUser",



              "routeAlterUser"
    ])]
    private Collection $listFavoris;

    /**
     * @var Collection<int, ListToDo> user's list to-do
     */
    #[ORM\OneToMany(mappedBy: "user", targetEntity: ListToDo::class, orphanRemoval: true)]
    #[Groups(["getAlterUser",



              "routeAlterUser"
    ])]
    private Collection $listToDos;

    /**
     * @var Collection<int, Room> user's rooms
     */
    #[ORM\ManyToMany(targetEntity: Room::class, mappedBy: "members")]
    private Collection $rooms;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiToken = null;

    /**
     * @var Collection<int, Achievement>
     */
    #[ORM\ManyToMany(targetEntity: Achievement::class, mappedBy: 'users')]
    private Collection $achievements;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getCurrent",
              "getAlterUser",
              "findFriends",
              "getRequestsAndFriendships",


              "getInformationsCurrentUser",
              "routeAlterUser"
    ])]
    private ?string $profilPic = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getCurrent",
              "getAlterUser",


              "getInformationsCurrentUser",
              "routeAlterUser"
    ])]
    private ?\DateTimeImmutable $birthday = null;

    #[ORM\Column]
    #[Groups(["getCurrent",
              "getAlterUser",


              "getInformationsCurrentUser",
              "routeAlterUser"
    ])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Avatar $avatar = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'blockedBy')]
    private Collection $userBlocked;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'userBlocked')]
    private Collection $blockedBy;

    public function __construct()
    {
        $this->listFavoris = new ArrayCollection();
        $this->listToDos = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->achievements = new ArrayCollection();
        $this->userBlocked = new ArrayCollection();
        $this->blockedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * need to authenticate
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getLevel(): ?float
    {
        return $this->level;
    }

    public function setLevel(?float $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getPronouns(): ?string
    {
        return $this->pronouns;
    }

    public function setPronouns(?string $pronouns): static
    {
        $this->pronouns = $pronouns;

        return $this;
    }

    public function getProfil(): ?string
    {
        return $this->profil;
    }

    public function setProfil(?string $profil): static
    {
        $this->profil = $profil;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): static
    {
        $this->validated = $validated;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

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
            $listFavori->setUser($this);
        }

        return $this;
    }

    public function removeListFavori(ListFavori $listFavori): static
    {
        if ($this->listFavoris->removeElement($listFavori)) {
            // set the owning side to null (unless already changed)
            if ($listFavori->getUser() === $this) {
                $listFavori->setUser(null);
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
            $listToDo->setUser($this);
        }

        return $this;
    }

    public function removeListToDo(ListToDo $listToDo): static
    {
        if ($this->listToDos->removeElement($listToDo)) {
            // set the owning side to null (unless already changed)
            if ($listToDo->getUser() === $this) {
                $listToDo->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->addMember($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            $room->removeMember($this);
        }

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection
    {
        return $this->achievements;
    }

    public function addAchievement(Achievement $achievement): static
    {
        if (!$this->achievements->contains($achievement)) {
            $this->achievements->add($achievement);
            $achievement->addUser($this);
        }

        return $this;
    }

    public function removeAchievement(Achievement $achievement): static
    {
        if ($this->achievements->removeElement($achievement)) {
            $achievement->removeUser($this);
        }

        return $this;
    }

    public function getProfilPic(): ?string
    {
        return $this->profilPic;
    }

    public function setProfilPic(?string $profilPic): static
    {
        $this->profilPic = $profilPic;

        return $this;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeImmutable $birthday): static
    {
        $this->birthday = $birthday;

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

    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function setAvatar(Avatar $avatar): static
    {
        // set the owning side of the relation if necessary
        if ($avatar->getUser() !== $this) {
            $avatar->setUser($this);
        }

        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUserBlocked(): Collection
    {
        return $this->userBlocked;
    }

    public function addUserBlocked(self $userBlocked): static
    {
        if (!$this->userBlocked->contains($userBlocked)) {
            $this->userBlocked->add($userBlocked);
        }

        return $this;
    }

    public function removeUserBlocked(self $userBlocked): static
    {
        $this->userBlocked->removeElement($userBlocked);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getBlockedBy(): Collection
    {
        return $this->blockedBy;
    }

    public function addBlockedBy(self $blockedBy): static
    {
        if (!$this->blockedBy->contains($blockedBy)) {
            $this->blockedBy->add($blockedBy);
            $blockedBy->addUserBlocked($this);
        }

        return $this;
    }

    public function removeBlockedBy(self $blockedBy): static
    {
        if ($this->blockedBy->removeElement($blockedBy)) {
            $blockedBy->removeUserBlocked($this);
        }

        return $this;
    }
}
