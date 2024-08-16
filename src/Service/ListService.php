<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Escape;
use App\Entity\ListDone;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Repository\ListDoneRepository;
use App\Repository\ListFavoriRepository;
use App\Repository\ListToDoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ListService
{
    private EntityManagerInterface $em;
    private ListFavoriRepository $favoriRep;
    private ListToDoRepository $toDoRep;

    public function __construct(
        EntityManagerInterface $em,
        ListFavoriRepository $favoriRep,
        ListToDoRepository $toDoRep
    ) {
        $this->em = $em;
        $this->favoriRep = $favoriRep;
        $this->toDoRep = $toDoRep;
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
}
