<?php

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\Link;
use App\Entity\Entreprise;
use App\Entity\Escape;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LinkTest extends KernelTestCase
{
    public function mockEntreprise(int $id): Entreprise
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Entreprise::class, $id);
    }

    public function mockEscape(int $id): Escape
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Escape::class, $id);
    }

    public function mockCity(int $id): City
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(City::class, $id);
    }

    public function getLink(): Link
    {
        $link = new Link();
        $link->setLink("Test link");
        $link->setCity($this->mockCity(1));
        $link->setEscape($this->mockEscape(1));
        $link->setEntreprise($this->mockEntreprise(1));

        return $link;
    }

    public function assertHasErrors(Link $link, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($link);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidLink(): void
    {
        $this->assertHasErrors($this->getLink(), 0);
    }

    public function testValidGetterLink(): void
    {
        $link = $this->getLink();
        $this->assertTrue($link->getLink() === "Test link");
    }

    public function testValidGetterId(): void
    {
        $link = $this->getLink();
        $this->assertNull($link->getId());
    }

    public function testInvalidBlanckLink(): void
    {
        $link = $this->getLink();
        $link->setLink("");
        $this->assertHasErrors($link, 1);
    }

    public function testValidGetCity(): void
    {
        $link = $this->getLink();
        $this->assertEquals($link->getCity(), $this->mockCity(1));
    }

    public function testValidGetEntreprise(): void
    {
        $link = $this->getLink();
        $this->assertEquals($link->getEntreprise(), $this->mockEntreprise(1));
    }

    public function testValidGetEscape(): void
    {
        $link = $this->getLink();
        $this->assertEquals($link->getEscape(), $this->mockEscape(1));
    }
}
