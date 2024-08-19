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
use Symfony\Component\Serializer\SerializerInterface;

class ListService
{
    private EntityManagerInterface $em;
    private ListFavoriRepository $favoriRep;
    private ListToDoRepository $toDoRep;
    private EscapeRepository $escapeRep;
    private UserRepository $userRep;
    private DoneSessionRepository $sessionRep;
    private SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $em,
        ListFavoriRepository $favoriRep,
        ListToDoRepository $toDoRep,
        EscapeRepository $escapeRep,
        UserRepository $userRep,
        DoneSessionRepository $sessionRep,
        SerializerInterface $serializer
    ) {
        $this->em = $em;
        $this->favoriRep = $favoriRep;
        $this->toDoRep = $toDoRep;
        $this->escapeRep = $escapeRep;
        $this->userRep = $userRep;
        $this->sessionRep = $sessionRep;
        $this->serializer = $serializer;
    }

    /**
     * Add an escape to current user's favori
     *
     * @param User $user current user
     * @param Escape $escape escape to add
     */
    public function addToFavori(User $user, Escape $escape): void
    {
        if ($this->favoriRep->isItAlreadyInList($user, $escape) === null) {
            $favori = new ListFavori();
            $now = new DateTime("now");
            $favori->setSince($now);
            $favori->setUser($user);
            $favori->setEscape($escape);

            $this->em->persist($favori);
            $this->em->flush();
        }
    }

    /**
     * Add an escape to current user's to-do
     *
     * @param User $user current user
     * @param Escape $escape escape to add
     */
    public function addToToDo(User $user, Escape $escape): void
    {
        if ($this->toDoRep->isItAlreadyInList($user, $escape) === null) {
            $toDo = new ListToDo();
            $now = new DateTime("now");
            $toDo->setSince($now);
            $toDo->setUser($user);
            $toDo->setEscape($escape);

            $this->em->persist($toDo);
            $this->em->flush();
        }
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
     * Add session
     *
     * @param User $user current user
     * @param array $informations members / date / escape
     *
     * @return string|null
     */
    public function addSession(User $user, array $informations): string|null
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
        $this->checkAndUpdateIfDoneEscapeIsInToDoList($user, $escape);
        foreach ($informations["members"] as $member) {
            $user = $this->userRep->findOneBy(["id" => $member]);
            $session->addMember($user);
            $this->checkAndUpdateIfDoneEscapeIsInToDoList($user, $escape);
        }
        $session->setPlayedAt(new DateTime($informations["date"]));

        $this->em->persist($session);
        $this->em->flush();

        return null;
    }

    /**
     * Get sessions
     *
     * @param User $user current user
     *
     * @return string
     */
    public function getSessions(User $user): string
    {
        $sessions = $this->sessionRep->findSessions($user);
        $json = $this->serializer->serialize($sessions, "json", ["groups" => "getSessions"]);

        return $json;
    }
    
    /**
     * Get sessions for escape
     *
     * @param User $user current user
     * @param Escape $escape escape
     *
     * @return string
     */
    public function getSessionsForEscape(User $user, Escape $escape): string
    {
        $sessions = $this->sessionRep->findSessionsByEscapeAndUser($user, $escape);
        $json = $this->serializer->serialize($sessions, "json", ["groups" => "getSessions"]);

        return $json;
    }
}
