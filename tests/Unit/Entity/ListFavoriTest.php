<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ListFavori;
use App\Entity\Escape;
use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ListFavoriTest extends KernelTestCase
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

    public function getListFavori(): ListFavori
    {
        $favori = new ListFavori();
        $favori->setSince($this->mockTime());
        $favori->setUser($this->mockUser(1));
        $favori->setEscape($this->mockEscape(1));

        return $favori;
    }

    public function assertHasErrors(ListFavori $favori, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($favori);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidListFavori(): void
    {
        $this->assertHasErrors($this->getListFavori(), 0);
    }

    public function testValidGetSince(): void
    {
        $favori = $this->getListFavori();
        $this->assertEquals($favori->getSince(), $this->mockTime());
    }

    public function testValidGetterId(): void
    {
        $favori = $this->getListFavori();
        $this->assertNull($favori->getId());
    }

    public function testValidGetUser(): void
    {
        $favori = $this->getListFavori();
        $this->assertEquals($favori->getUser(), $this->mockUser(1));
    }

    public function testValidGetEscape(): void
    {
        $favori = $this->getListFavori();
        $this->assertEquals($favori->getEscape(), $this->mockEscape(1));
    }
}
