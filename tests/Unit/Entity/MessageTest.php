<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\Room;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessageTest extends KernelTestCase
{
    public function mockRoom(int $id): Room
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Room::class, $id);
    }

    public function mockUser(int $id): User
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(User::class, $id);
    }

    public function mockTime(): DateTimeImmutable
    {
        return new DateTimeImmutable("24-05-2024");
    }

    public function getMessage(): Message
    {
        $message = new Message();
        $message->setMessage("Test message");
        $message->setCreatedAt($this->mockTime());
        $message->setSender($this->mockUser(1));
        $message->addReadBy($this->mockUser(1));
        $message->addReadBy($this->mockUser(2));
        $message->setRoom($this->mockRoom(3));
        return $message;
    }

    public function assertHasErrors(Message $message, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($message);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidGetterId(): void
    {
        $message = $this->getMessage();
        $this->assertNull($message->getId());
    }

    public function testValidGetRoom(): void
    {
        $message = $this->getMessage();
        $this->assertEquals($message->getRoom(), $this->mockRoom(3));
    }

    public function testValidCreatedAt(): void
    {
        $message = $this->getMessage();
        $this->assertEquals($message->getCreatedAt(), $this->mockTime());
    }

    public function testValidGetSender(): void
    {
        $message = $this->getMessage();
        $this->assertEquals($message->getSender(), $this->mockUser(1));
    }

    public function testValidGetMessage(): void
    {
        $message = $this->getMessage();
        $this->assertEquals($message->getMessage(), "Test message");
    }

    public function testValidRemoveReadBy(): void
    {
        $message = $this->getMessage();
        $message->removeReadBy($this->mockUser(2));
        $this->assertCount(1, $message->getReadBy());
    }
}
