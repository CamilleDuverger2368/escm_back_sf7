<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Achievement;
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
     * @return array<Achievement>
     */
    public function hasAchievementToUnlock(string $type, User $user): array
    {
        $achievements = $this->achievementRep->getAchievementsToUnlockedOfTypeByUser($type, $user);
        return $achievements;
    }

    /**
     * Check achievement to unlock for a user
     *
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getAchievementsToUnlock(User $user): array
    {
        $achievements = $this->achievementRep->getAchievementsToUnlocked($user);
        return $achievements;
    }

    /**
     * Check if User has unlocked achievements of this type
     *
     * @param string $type achievement's type
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function hasUnlockedAchievement(string $type, User $user): array
    {
        $achievements = $this->achievementRep->getUnlockedAchievementsOfTypeByUser($type, $user);
        return $achievements;
    }

    /**
     * Get unlocked achievements for current user
     *
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedAchievements(User $user): array
    {
        $achievements = $this->achievementRep->getUnlockedAchievements($user);
        return $achievements;
    }
}
