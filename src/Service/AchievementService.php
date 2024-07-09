<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\AchievementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class AchievementService
{
    private AchievementRepository $achievementRep;
    private EntityManagerInterface $em;

    public function __construct(
        AchievementRepository $achievementRep,
        EntityManagerInterface $em
    ) {
        $this->achievementRep = $achievementRep;
        $this->em = $em;
    }

    /**
     * Check if User has a new achievement of this type unlocked
     *
     * @param string $type achievement's type
     * @param User $user current user
     *
     * @return string
     */
    public function hasAchievementToUnlock(string $type, User $user): string
    {
        // DEBUG !!!
        $achievementsUnlocked = $this->achievementRep->getAchievementsUnlockedOfTypeByUser($type, $user);
        dd($achievementsUnlocked);
        return "coucou";
        // DEBUG !!!
    }
}
