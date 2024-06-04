<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\Room;
use App\Entity\User;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MessageService
{
    private RoomRepository $roomRep;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;

    public function __construct(
        RoomRepository $roomRep,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ) {
        $this->roomRep = $roomRep;
        $this->serializer = $serializer;
        $this->em = $em;
    }

    /**
     * Check if a message is read by User
     *
     * @param User $user sender
     * @param Message $message message
     *
     * @return bool
     */
    public function isRead(User $user, Message $message): bool
    {
        foreach ($message->getReadBy() as $reader) {
            if ($reader === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * Send a message
     *
     * @param User $user sender
     * @param Room $room room
     * @param string $content message
     */
    public function sendMessage(User $user, Room $room, string $content): void
    {
        $message = new Message();
        $message->setSender($user);
        $message->addReadBy($user);
        $message->setRoom($room);
        $message->setMessage($content);

        $this->em->persist($message);
        $this->em->flush();
    }

    /**
     * Get all unread message and rooms of current user
     *
     * @param User $user current user
     *
     * @return string
     */
    public function getRoomsAndUnreadMessages(User $user): string
    {
        $result = [];
        $rooms = $this->roomRep->getRoomWhereUserIsMember($user);
        foreach ($rooms as $room) {
            $count = 0;
            foreach ($room->getMessages() as $message) {
                if ($this->isRead($user, $message) === false) {
                    $count++;
                }
            }
            if ($result === []) {
                $result = array_merge(["room" => $room], ["unread_message" => $count]);
                $result = [$result];
            } else {
                $tmpResult = array_merge(["room" => $room], ["unread_message" => $count]);
                $tmpResult = [$tmpResult];
                $tmp = array_merge($result, $tmpResult);
                $result = $tmp;
            }
        }
        $json = $this->serializer->serialize($result, "json", ["groups" => "getRoom"]);
        return $json;
    }

    /**
     * Read all room's unread message
     *
     * @param User $user sender
     * @param Room $room room
     */
    public function readMessagesOfRoom(User $user, Room $room): void
    {
        foreach ($room->getMessages() as $message) {
            if ($this->isRead($user, $message) === false) {
                $message->addReadBy($user);
                $this->em->persist($message);
            }
        }
        $this->em->flush();
    }
}
