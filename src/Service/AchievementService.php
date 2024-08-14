<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Achievement;
use App\Repository\AchievementRepository;
use App\Repository\GradeRepository;
use App\Repository\ListDoneRepository;
use App\Repository\ListToDoRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;

class AchievementService
{
    private AchievementRepository $achievementRep;
    private GradeRepository $gradeRep;
    private RoomRepository $roomRep;
    private ListToDoRepository $toDoRep;
    private ListDoneRepository $doneRep;
    private EntityManagerInterface $em;

    public function __construct(
        AchievementRepository $achievementRep,
        GradeRepository $gradeRep,
        RoomRepository $roomRep,
        ListToDoRepository $toDoRep,
        ListDoneRepository $doneRep,
        EntityManagerInterface $em
    ) {
        $this->achievementRep = $achievementRep;
        $this->gradeRep = $gradeRep;
        $this->roomRep = $roomRep;
        $this->toDoRep = $toDoRep;
        $this->doneRep = $doneRep;
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

    /**
     * Get unlocked objects3D for current user
     *
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedObjects3D(User $user): array
    {
        $achievements = $this->achievementRep->getUnlockedObjects3D($user);
        return $achievements;
    }

    /**
     * Get unlocked pictures for current user
     *
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedPictures(User $user): array
    {
        $achievements = $this->achievementRep->getUnlockedPictures($user);
        return $achievements;
    }

    /**
     * Check achievements
     *
     * @param User $user current user
     * @param array<int, Achievement> $achievements current user
     *
     * @return void
     */
    public function checkToUnlockAchievements(User $user, array $achievements): void
    {
        foreach ($achievements as $achievement) {
            $this->{$achievement->getChecker()}($user, $achievement);
        }
    }

    // FUNCTIONS TO CHECK / VALIDATE ACHIEVEMENTS

    /**
     * Check "Le plus bel endroit du monde"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onWorseGrade(User $user, Achievement $achievement): void
    {
        $grades = $this->gradeRep->findBy(["user" => $user]);
        foreach ($grades as $grade) {
            if ($grade->getGrade() === 1) {
                $achievement->addUser($user);
                $this->em->persist($achievement);
                $this->em->flush();
                break ;
            }
        }
    }

    /**
     * Check "Bien fichu, ce bazar quand même"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onBetterGrade(User $user, Achievement $achievement): void
    {
        $grades = $this->gradeRep->findBy(["user" => $user]);
        foreach ($grades as $grade) {
            if ($grade->getGrade() === 5) {
                $achievement->addUser($user);
                $this->em->persist($achievement);
                $this->em->flush();
                break ;
            }
        }
    }

    /**
     * Check "Ohlala la jugeance !"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onFirstGrade(User $user, Achievement $achievement): void
    {
        $grades = $this->gradeRep->findBy(["user" => $user]);
        if (count($grades) > 0) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "Salut copain !"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onFirstContact(User $user, Achievement $achievement): void
    {
        $rooms = $this->roomRep->getRoomWhereUserIsMember($user);
        if (count($rooms) > 0) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "Tous ensemble !"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onCreateTeam(User $user, Achievement $achievement): void
    {
        $rooms = $this->roomRep->getRoomWhereUserIsMember($user);
        foreach ($rooms as $room) {
            if (count($room->getMembers()) > 2) {
                $achievement->addUser($user);
                $this->em->persist($achievement);
                $this->em->flush();
                break ;
            }
        }
    }

    /**
     * Check "Le sacrosaint quatuor"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onCreatePerfectTeam(User $user, Achievement $achievement): void
    {
        $rooms = $this->roomRep->getRoomWhereUserIsMember($user);
        foreach ($rooms as $room) {
            if (count($room->getMembers()) === 4) {
                $achievement->addUser($user);
                $this->em->persist($achievement);
                $this->em->flush();
                break ;
            }
        }
    }

    /**
     * Check "Viens ! On est déjà 9 !"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onCreateBigTeam(User $user, Achievement $achievement): void
    {
        $rooms = $this->roomRep->getRoomWhereUserIsMember($user);
        foreach ($rooms as $room) {
            if (count($room->getMembers()) >= 10) {
                $achievement->addUser($user);
                $this->em->persist($achievement);
                $this->em->flush();
                break ;
            }
        }
    }

    /**
     * Check "Première quête !"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onAddToToDoList(User $user, Achievement $achievement): void
    {
        $toDos = $this->toDoRep->findBy(["user" => $user]);
        if (count($toDos) > 0) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "Premiers pas"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onCompleteRegister(User $user, Achievement $achievement): void
    {
        $complete = true;

        if ($user->getPseudo() === null) {
            $complete = false;
        }
        if ($user->getAge() === null) {
            $complete = false;
        }
        if ($user->getPronouns() === null) {
            $complete = false;
        }
        if ($user->getProfil() === null) {
            $complete = false;
        }

        if ($complete) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "10/10"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onTenEscapeDone(User $user, Achievement $achievement): void
    {
        $dones = $this->doneRep->findBy(["user" => $user]);
        if (count($dones) >= 10) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "Maître du donjon"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onFitfyEscapeDone(User $user, Achievement $achievement): void
    {
        $dones = $this->doneRep->findBy(["user" => $user]);
        if (count($dones) >= 50) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "Welcome aboard TERPECA"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onHundredEscapeDone(User $user, Achievement $achievement): void
    {
        $dones = $this->doneRep->findBy(["user" => $user]);
        if (count($dones) >= 100) {
            $achievement->addUser($user);
            $this->em->persist($achievement);
            $this->em->flush();
        }
    }

    /**
     * Check "Travail terminé"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onFinishFirstEscapeWish(User $user, Achievement $achievement): void
    {
        $todos = $this->toDoRep->findBy(["user" => $user]);
        $dones = $this->doneRep->findBy(["user" => $user]);
        $found = false;
        foreach ($dones as $done) {
            foreach ($todos as $todo) {
                if ($todo->getEscape() === $done->getEscape()) {
                    if ($todo->getSince() < $done->getSince()) {
                        $achievement->addUser($user);
                        $this->em->persist($achievement);
                        $this->em->flush();
                        $found = true;
                        break ;
                    }
                }
            }
            if ($found) {
                break ;
            }
        }
    }

    /**
     * Check "I'll be back !"
     *
     * @param User $user current user
     * @param Achievement $achievement achievement to check
     *
     * @return void
     */
    public function onGoBackAgain(User $user, Achievement $achievement): void
    {
        $todos = $this->toDoRep->findBy(["user" => $user]);
        $dones = $this->doneRep->findBy(["user" => $user]);
        $found = false;
        foreach ($dones as $done) {
            foreach ($todos as $todo) {
                if ($todo->getEscape() === $done->getEscape()) {
                    if ($todo->getSince() > $done->getSince()) {
                        $achievement->addUser($user);
                        $this->em->persist($achievement);
                        $this->em->flush();
                        $found = true;
                        break ;
                    }
                }
            }
            if ($found) {
                break ;
            }
        }
    }
}
