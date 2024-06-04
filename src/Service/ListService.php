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
    private ListDoneRepository $doneRep;

    public function __construct(
        EntityManagerInterface $em,
        ListFavoriRepository $favoriRep,
        ListToDoRepository $toDoRep,
        ListDoneRepository $doneRep,
    ) {
        $this->em = $em;
        $this->favoriRep = $favoriRep;
        $this->toDoRep = $toDoRep;
        $this->doneRep = $doneRep;
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
     * Add an escape to current user's done
     *
     * @param User $user current user
     * @param Escape $escape escape to add
     */
    public function addToDone(User $user, Escape $escape): void
    {
        if ($this->doneRep->isItAlreadyInList($user, $escape) === null) {
            // Create ligne in user's list
            $done = new ListDone();
            $now = new DateTime("now");
            $done->setSince($now);
            $done->setUser($user);
            $done->setEscape($escape);

            // Give some Xp to current user
            if ($user->getLevel() == null) {
                $user->setLevel(0.25);
            } else {
                $user->setLevel($user->getLevel() + 0.25);
            }

            $this->em->persist($done);
            $this->em->persist($user);
            $this->em->flush();
        }
    }

    /**
     * Add an escape to current user's done
     *
     * @param User $user current user
     * @param ListDone $done list to remove
     */
    public function removeFromDone(User $user, ListDone $done): void
    {
        // Withdraw some Xp to current user
        if ($user->getLevel() <= 0.25) {
            $user->setLevel(null);
        } else {
            $user->setLevel($user->getLevel() - 0.25);
        }

        $this->em->persist($user);
        $this->em->remove($done);
        $this->em->flush();
    }
}
