<?php

namespace App\Service;

use App\Entity\DoneSession;
use App\Entity\User;
use App\Entity\Escape;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Repository\DoneSessionRepository;
use App\Repository\EscapeRepository;
use App\Repository\ListFavoriRepository;
use App\Repository\ListToDoRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ListService
{
    private EntityManagerInterface $em;
    private ListFavoriRepository $favoriRep;
    private ListToDoRepository $toDoRep;
    private EscapeRepository $escapeRep;
    private UserRepository $userRep;
    private DoneSessionRepository $sessionRep;
    private AchievementService $achievementService;

    public function __construct(
        EntityManagerInterface $em,
        ListFavoriRepository $favoriRep,
        ListToDoRepository $toDoRep,
        EscapeRepository $escapeRep,
        UserRepository $userRep,
        DoneSessionRepository $sessionRep,
        AchievementService $achievementService
    ) {
        $this->em = $em;
        $this->favoriRep = $favoriRep;
        $this->toDoRep = $toDoRep;
        $this->escapeRep = $escapeRep;
        $this->userRep = $userRep;
        $this->sessionRep = $sessionRep;
        $this->achievementService = $achievementService;
    }

    /**
     * Update to-do list done escape is in user's to-do list
     *
     * @param User $user
     * @param Escape $escape
     */
    public function checkAndUpdateIfDoneEscapeIsInToDoList(User $user, Escape $escape): void
    {
        if ($todo = $this->toDoRep->isItAlreadyInList($user, $escape)) {
            $this->em->remove($todo);
            $this->em->flush();
        }
    }

    /**
     * Get user's favoris
     *
     * @param User $user current user
     *
     * @return array<int,ListFavori>
     */
    public function getUserFavoris(User $user): array
    {
        $favoris = $this->favoriRep->getByUser($user);

        return $favoris;
    }

    /**
     * Get user's to-dos
     *
     * @param User $user current user
     *
     * @return array<int,ListToDo>
     */
    public function getUserToDos(User $user): array
    {
        $toDo = $this->toDoRep->getByUser($user);

        return $toDo;
    }

    /**
     * Get user's sessions
     *
     * @param User $user user
     *
     * @return array<int, DoneSession>
     */
    public function getUserSessions(User $user): array
    {
        $sessions = $this->sessionRep->findSessions($user);

        return $sessions;
    }

    /**
     * Get sessions for escape
     *
     * @param User $user current user
     * @param Escape $escape escape
     *
     * @return array<int,DoneSession>
     */
    public function getSessionsForEscape(User $user, Escape $escape): array
    {
        $sessions = $this->sessionRep->findSessionsByEscapeAndUser($user, $escape);

        return $sessions;
    }

    /**
     * Add an escape to current user's favori
     *
     * @param User $user current user
     * @param Escape $escape escape to add
     *
     * @return ListFavori|null
     */
    public function addToFavori(User $user, Escape $escape): ?ListFavori
    {
        if ($this->favoriRep->isItAlreadyInList($user, $escape) === null) {
            $favori = new ListFavori();
            $now = new DateTime("now");
            $favori->setSince($now);
            $favori->setUser($user);
            $favori->setEscape($escape);

            return $favori;
        }
        return null;
    }

    /**
     * Add an escape to current user's to-do
     *
     * @param User $user current user
     * @param Escape $escape escape to add
     *
     * @return ListToDo|null
     */
    public function addToToDo(User $user, Escape $escape): ?ListToDo
    {
        if ($this->toDoRep->isItAlreadyInList($user, $escape) === null) {
            $toDo = new ListToDo();
            $now = new DateTime("now");
            $toDo->setSince($now);
            $toDo->setUser($user);
            $toDo->setEscape($escape);

            return $toDo;
        }

        return null;
    }

    /**
     * Add session
     *
     * @param User $user current user
     * @param array $informations members / date / escape
     *
     * @return DoneSession|string
     */
    public function addSession(User $user, array $informations): DoneSession|string
    {
        if (!$informations["date"]) {
            return "Need session's date.";
        }
        if (!$informations["escape"]) {
            return "Need escape to add.";
        }

        $session = new DoneSession();
        if (null === $escape = $this->escapeRep->findOneBy(["id" => $informations["escape"]])) {
            return "Need escape to add.";
        }
        $session->setEscape($escape);
        $session->addMember($user);

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        $this->checkAndUpdateIfDoneEscapeIsInToDoList($user, $escape);
        foreach ($informations["members"] as $member) {
            $user = $this->userRep->findOneBy(["id" => $member]);
            $session->addMember($user);

            // Check achievements
            if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
                $this->achievementService->checkToUnlockAchievements($user, $achievements);
            }

            $this->checkAndUpdateIfDoneEscapeIsInToDoList($user, $escape);
        }
        $session->setPlayedAt(new DateTime($informations["date"]));

        return $session;
    }
}
