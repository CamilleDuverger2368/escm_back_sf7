<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ListDone;
use App\Entity\Escape;
use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ListDoneTest extends KernelTestCase
{
    public function mockEscape(int $id): Escape
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Escape::class, $id);
    }

    public function mockUser(int $id): User
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(User::class, $id);
    }

    public function mockTime(): DateTime
    {
        return new DateTime("24-05-2024");
    }

    public function getListDone(): ListDone
    {
        $done = new ListDone();
        $done->setSince($this->mockTime());
        $done->setUser($this->mockUser(1));
        $done->setEscape($this->mockEscape(1));

        return $done;
    }

    public function assertHasErrors(ListDone $done, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($done);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidListDone(): void
    {
        $this->assertHasErrors($this->getListDone(), 0);
    }

    public function testValidGetSince(): void
    {
        $done = $this->getListDone();
        $this->assertEquals($done->getSince(), $this->mockTime());
    }

    public function testValidGetterId(): void
    {
        $done = $this->getListDone();
        $this->assertNull($done->getId());
    }

    public function testValidGetUser(): void
    {
        $done = $this->getListDone();
        $this->assertEquals($done->getUser(), $this->mockUser(1));
    }

    public function testValidGetEscape(): void
    {
        $done = $this->getListDone();
        $this->assertEquals($done->getEscape(), $this->mockEscape(1));
    }
}
