<?php

namespace App\Service;

use App\Entity\Room;
use App\Entity\User;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoomService
{
    private UserRepository $userRep;
    private RoomRepository $roomRep;
    private EntityManagerInterface $em;

    public function __construct(
        UserRepository $userRep,
        RoomRepository $roomRep,
        EntityManagerInterface $em
    ) {
        $this->userRep = $userRep;
        $this->roomRep = $roomRep;
        $this->em = $em;
    }

    /**
     * Check if User is a room's member
     *
     * @param User $user current user
     * @param Room $room room
     *
     * @return bool
     */
    public function isMember(User $user, Room $room): bool
    {
        $found = false;
        foreach ($room->getMembers() as $member) {
            if ($member === $user) {
                $found = true;
            }
        }

        return $found;
    }

    /**
     * Check if User is a room's admin
     *
     * @param User $user current user
     * @param Room $room room
     *
     * @return bool
     */
    public function isAdmin(User $user, Room $room): bool
    {
        $found = false;
        foreach ($room->getAdmins() as $member) {
            if ($member === $user) {
                $found = true;
            }
        }

        return $found;
    }

    /**
     * Update Room's name
     *
     * @param User $user current user
     * @param Room $room room
     * @param string|null $name room's new name
     *
     * @return bool
     */
    public function roomNewName(User $user, Room $room, ?string $name): bool
    {
        if ($this->isMember($user, $room) === false || isset($name) === false) {
            return false;
        }
        $room->setName($name);
        $this->em->persist($room);
        $this->em->flush();
        return true;
    }

    /**
     * Add Room mate
     *
     * @param User $user current user
     * @param Room $room room
     * @param int|null $memberId new member
     *
     * @return string|null
     */
    public function addRoomMate(User $user, Room $room, ?int $memberId): ?string
    {
        if ($this->isAdmin($user, $room) === false) {
            return "User is not admin.";
        }
        if (null === $member = $this->userRep->findOneBy(["id" => $memberId])) {
            return "Member not found.";
        }
        if ($this->isMember($member, $room) === false) {
            $room->addMember($member);
            $this->em->persist($room);
            $this->em->flush();
        }
        return null;
    }

    /**
     * Granted a member Admin
     *
     * @param User $user current user
     * @param Room $room room
     * @param int|null $memberId new admin
     *
     * @return string|null
     */
    public function grantedMemberAdmin(User $user, Room $room, ?int $memberId): ?string
    {
        if ($this->isAdmin($user, $room) === false) {
            return "User is not admin.";
        }
        if (null === $member = $this->userRep->findOneBy(["id" => $memberId])) {
            return "Member not found.";
        }
        if ($this->isMember($member, $room) === false) {
            return "This user is not a member of the room " . $room->getId();
        }
        if ($this->isAdmin($member, $room) === false) {
            $room->addAdmin($member);
            $this->em->persist($room);
            $this->em->flush();
        }
        return null;
    }

    /**
     * Kick-off a member
     *
     * @param User $user current user
     * @param Room $room room
     * @param int|null $memberId new admin
     *
     * @return string|null
     */
    public function kickOffFrom(User $user, Room $room, ?int $memberId): ?string
    {
        if ($this->isAdmin($user, $room) === false) {
            return "User is not admin.";
        }
        if (null === $member = $this->userRep->findOneBy(["id" => $memberId])) {
            return "Member not found.";
        }
        if ($this->isMember($member, $room) === false) {
            return "This user is not a member of the room " . $room->getId();
        }
        if ($this->isAdmin($member, $room) === true) {
            $room->removeAdmin($member);
        }
        $room->removeMember($member);
        $this->em->persist($room);
        $this->em->flush();
        return null;
    }

    /**
     * Quit a room
     *
     * @param User $user current user
     * @param Room $room room
     *
     * @return string|null
     */
    public function quitRoom(User $user, Room $room): ?string
    {
        if ($this->isMember($user, $room) === false) {
            return "This user is not a member of the room " . $room->getId();
        }
        $checkForAdmin = false;
        if ($this->isAdmin($user, $room) === true) {
            $room->removeAdmin($user);
            if (count($room->getAdmins()) === 0) {
                $checkForAdmin = true;
            }
        }
        $room->removeMember($user);
        if ($checkForAdmin === true) {
            if (count($members = $room->getMembers()) === 0) {
                foreach ($room->getMessages() as $message) {
                    $this->em->remove($message);
                }
                $this->em->remove($room);
                $this->em->flush();
                return null;
            }
            if ($members[0]) {
                $room->addAdmin($members[0]);
            }
        }
        $this->em->persist($room);
        $this->em->flush();
        return null;
    }

    /**
     * Give usage name of user on tchat
     *
     * @param User $user user
     *
     * @return string
     */
    public function getRoomMemberName(User $user): string
    {
        if ($user->getPseudo()) {
            return $user->getPseudo();
        }
        $name = $user->getFirstName() . ' ' . $user->getName();
        return $name;
    }

    /**
     * Check if a room with these members exists
     *
     * @param User $user current user
     * @param array<int, User> $members room's members
     *
     * @return Room|null
     */
    public function isRoomExist(User $user, array $members): ?Room
    {
        // To-Do : a opti en sql, faire toute la recherche en sql --> faisable faut juiste se pencher dessus
        $rooms = $this->roomRep->getRoomsWhereMembersAre($user, $members);
        foreach ($rooms as $room) {
            if (count($room->getMembers()) === count($members) + 1) {
                $found = true;
                foreach ($members as $member) {
                    if ($this->isMember($member, $room) === false) {
                        $found = false;
                        break ;
                    }
                    if ($found === true && $this->isMember($user, $room) === true) {
                        return $room;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Create a room
     *
     * @param Room $room room to create
     * @param User $user current user
     * @param array<int, User> $members room's members
     * @param string|null $roomName room's name
     */
    public function createRoom(Room $room, User $user, array $members, ?string $roomName): void
    {
        // Add current user as Admin and Member
        $room->addMember($user);
        $room->addAdmin($user);

        // Check if it's a private room
        $privateRoom = false;
        if (count($members) === 1) {
            $privateRoom = true;
        }

        // Add members to the room and create a name
        $name = $this->getRoomMemberName($user) . ", ";
        foreach ($members as $key => $member) {
            if ($privateRoom) {
                $room->addAdmin($member);
            }
            $room->addMember($member);

            $name .= $this->getRoomMemberName($member);
            if ($key !== array_key_last($members)) {
                $name .= ", ";
            }
        }

        isset($roomName) ? $room->setName($roomName) : $room->setName($name);

        $this->em->persist($room);
        $this->em->flush();
    }
}
