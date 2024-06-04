<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\Room;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoomTest extends KernelTestCase
{
    public function mockMessage(int $id): Message
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Message::class, $id);
    }

    public function mockUser(int $id): User
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(User::class, $id);
    }

    public function mockTime(): DateTimeImmutable
    {
        return new DateTimeImmutable("24-05-2024");
    }

    public function getRoom(): Room
    {
        $room = new Room();
        $room->setName("Test room");
        $room->setCreatedAt($this->mockTime());
        $room->addAdmin($this->mockUser(1));
        $room->addAdmin($this->mockUser(2));
        $room->addMember($this->mockUser(1));
        $room->addMember($this->mockUser(2));
        $room->addMessage($this->mockMessage(1));
        $room->addMessage($this->mockMessage(2));
        $room->addMessage($this->mockMessage(3));
        return $room;
    }

    public function assertHasErrors(Room $room, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($room);
        $rooms = [];
        foreach ($errors as $error) {
            $rooms[] = $error->getPropertyPath() . " => " . $error->getRoom();
        }
        $this->assertCount($nb, $errors, implode(", ", $rooms));
    }

    public function testValidGetterId(): void
    {
        $room = $this->getRoom();
        $this->assertNull($room->getId());
    }

    public function testValidName(): void
    {
        $room = $this->getRoom();
        $this->assertEquals($room->getName(), "Test room");
    }

    public function testValidCreatedAt(): void
    {
        $room = $this->getRoom();
        $this->assertEquals($room->getCreatedAt(), $this->mockTime());
    }

    public function testValidRemoveAdmin(): void
    {
        $room = $this->getRoom();
        $room->removeAdmin($this->mockUser(2));
        $this->assertCount(1, $room->getAdmins());
    }

    public function testValidRemoveMember(): void
    {
        $room = $this->getRoom();
        $room->removeMember($this->mockUser(2));
        $this->assertCount(1, $room->getMembers());
    }

    public function testValidRemoveMessage(): void
    {
        $room = $this->getRoom();
        $room->removeMessage($this->mockMessage(2));
        $this->assertCount(2, $room->getMessages());
    }
}
