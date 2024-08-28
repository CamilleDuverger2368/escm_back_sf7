<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Avatar;
use App\Repository\AvatarRepository;

class AvatarService
{
    private AvatarRepository $avatarRep;

    public function __construct(
        AvatarRepository $avatarRep,
    ) {
        $this->avatarRep = $avatarRep;
    }

    /**
     * Create avatar for user
     *
     * @param User $user user
     *
     * @return Avatar
     */
    public function createAvatar(User $user): Avatar
    {
        $avatar = new Avatar();
        $avatar->setCreatedAt(new \DateTimeImmutable());
        $avatar->setUser($user);

        return $avatar;
    }

    /**
     * Get user's avatar
     *
     * @param User $user user
     *
     * @return Avatar|string
     */
    public function getUserAvatar(User $user): Avatar|string
    {
        $avatar = $this->avatarRep->findOneBy(["user" => $user]);

        if ($avatar === null) {
            return "Avatar not found.";
        }

        return $avatar;
    }
}
