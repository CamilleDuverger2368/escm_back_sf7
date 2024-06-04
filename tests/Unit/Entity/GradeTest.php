<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Grade;
use App\Entity\Escape;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GradeTest extends KernelTestCase
{
    public function mockEscape(int $id): Escape
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Escape::class, $id);
    }

    public function mockUser(int $id): User
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(User::class, $id);
    }

    public function getGrade(): Grade
    {
        $grade = new Grade();
        $grade->setGrade(4);
        $grade->setUser($this->mockUser(1));
        $grade->setEscape($this->mockEscape(1));

        return $grade;
    }

    public function assertHasErrors(Grade $grade, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($grade);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidGrade(): void
    {
        $this->assertHasErrors($this->getGrade(), 0);
    }

    public function testValidGetterGrade(): void
    {
        $grade = $this->getGrade();
        $this->assertEquals($grade->getGrade(), 4);
    }

    public function testValidGetterId(): void
    {
        $grade = $this->getGrade();
        $this->assertNull($grade->getId());
    }

    public function testValidGetUser(): void
    {
        $grade = $this->getGrade();
        $this->assertEquals($grade->getUser(), $this->mockUser(1));
    }

    public function testValidGetEscape(): void
    {
        $grade = $this->getGrade();
        $this->assertEquals($grade->getEscape(), $this->mockEscape(1));
    }
}
