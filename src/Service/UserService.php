<?php

namespace App\Service;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserService
{
    private CityRepository $cityRep;
    private UserPasswordHasherInterface $userPasswordHasher;
    private UserRepository $userRep;
    private FriendshipRepository $friendshipRep;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;

    public function __construct(
        CityRepository $cityRep,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRep,
        FriendshipRepository $friendshipRep,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ) {
        $this->cityRep = $cityRep;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRep = $userRep;
        $this->friendshipRep = $friendshipRep;
        $this->serializer = $serializer;
        $this->em = $em;
    }

    /**
     * Check user's informations
     *
     * @param User $user user
     * @param array<string> $content user's informations
     *
     * @return string
     */
    public function checkInformationsUser(User $user, array $content): ?string
    {
        if (!$content["city"]) {
            return "need city";
        }
        if (!$city = $this->cityRep->findOneBy(["name" => $content["city"]])) {
            return "cant find this city";
        }
        if (!$content["password"]) {
            return "need password";
        }

        $user->setCity($city);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $content["password"]));
        $user->setValidated(false);
        $link = uniqid("", true);
        $user->setLink($link);
        $user->setCreatedAt(new \DateTimeImmutable());

        return null;
    }

    /**
     * Check user's informations for update
     *
     * @param User $user user
     * @param array<string> $content user's informations
     *
     * @return string
     */
    public function checkInformationsUserUpdate(User $user, array $content): ?string
    {
        if (!$content["city"]) {
            return "need city";
        }
        if (!$city = $this->cityRep->findOneBy(["name" => $content["city"]])) {
            return "cant find this city";
        }

        $user->setCity($city);

        return null;
    }

    /**
     * Update user's password
     *
     * @param User $user user
     * @param array<string> $content user's informations
     *
     * @return string
     */
    public function updatePasswordCurrentUser(User $user, array $content): ?string
    {
        if (!$content["password"]) {
            return "need password";
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $content["password"]));

        return null;
    }

    /**
     * Check if email is in DB
     *
     * @param string $email email
     *
     * @return User | null
     */
    public function checkIfEmailIsKnown(string $email): ?User
    {
        if (!$user = $this->userRep->findOneBy(["email" => $email])) {
            return null;
        }

        return $user;
    }

    /**
     * Reset user's password
     *
     * @param User $user user
     *
     * @return string
     */
    public function resetPassword(User $user): string
    {
        $digits = array_flip(range('0', '9'));
        $lowercase = array_flip(range('a', 'z'));
        $uppercase = array_flip(range('A', 'Z'));
        $special = array_flip(str_split("#?!@$%^&*-"));
        $combined = array_merge($digits, $lowercase, $uppercase, $special);
        $password = str_shuffle(array_rand($digits)
                                . array_rand($lowercase)
                                . array_rand($uppercase)
                                . array_rand($special)
                                . implode(array_rand($combined, rand(4, 8))));

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        return $password;
    }

    /**
     * Get an array of user from array of Users' id
     *
     * @param array<int> $users users' id
     *
     * @return array<User>|string
     */
    public function getUsersFromId(array $users): array|string
    {
        $members = [];
        foreach ($users as $userId) {
            if (!$user = $this->userRep->findOneBy(["id" => $userId])) {
                return "Member " . $userId . " not found";
            }
            array_push($members, $user);
        }
        return $members;
    }

    /**
     * Get an array of user from a search
     *
     * @param string $search name, firstname or pseudo
     * @param User $user current user
     *
     * @return array<User>
     */
    public function getUsersByNameOrPseudo(string $search, User $user): array
    {
        return $this->userRep->getUsersByNameOrPseudo($search, $user);
    }

    /**
     * Create a friend request
     *
     * @param User $sender current user
     * @param User $receiver futur friend ?
     *
     * @return string
     */
    public function friendRequest(User $sender, User $receiver): string
    {
        $asking = new Friendship();
        $asking->setSender($sender);
        $asking->setReceiver($receiver);
        $asking->setFriend(false);

        $this->em->persist($asking);
        $this->em->flush();

        $json = $this->serializer->serialize($asking, "json", ["groups" => "getAlterUser"]);

        return $json;
    }

    /**
     * Get alter profil
     *
     * @param User $current current user
     * @param User $user alter user
     *
     * @return string
     */
    public function getAlterProfil(User $current, User $user): ?string
    {
        $friendship = $this->friendshipRep->searchStatusFriendship($current, $user);
        $data = array_merge(
            ["user" => $user],
            ["friendship" => $friendship]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "getAlterUser"]);

        return $json;
    }
}
