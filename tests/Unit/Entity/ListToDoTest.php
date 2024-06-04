<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ListToDo;
use App\Entity\Escape;
use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ListToDoTest extends KernelTestCase
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

    public function getListToDo(): ListToDo
    {
        $toDo = new ListToDo();
        $toDo->setSince($this->mockTime());
        $toDo->setUser($this->mockUser(1));
        $toDo->setEscape($this->mockEscape(1));

        return $toDo;
    }

    public function assertHasErrors(ListToDo $toDo, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($toDo);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidListToDo(): void
    {
        $this->assertHasErrors($this->getListToDo(), 0);
    }

    public function testValidGetSince(): void
    {
        $toDo = $this->getListToDo();
        $this->assertEquals($toDo->getSince(), $this->mockTime());
    }

    public function testValidGetterId(): void
    {
        $toDo = $this->getListToDo();
        $this->assertNull($toDo->getId());
    }

    public function testValidGetUser(): void
    {
        $toDo = $this->getListToDo();
        $this->assertEquals($toDo->getUser(), $this->mockUser(1));
    }

    public function testValidGetEscape(): void
    {
        $toDo = $this->getListToDo();
        $this->assertEquals($toDo->getEscape(), $this->mockEscape(1));
    }
}
