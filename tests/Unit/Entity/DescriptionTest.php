<?php

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\Description;
use App\Entity\Entreprise;
use App\Entity\Escape;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DescriptionTest extends KernelTestCase
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

    public function getDescription(): Description
    {
        $description = new Description();
        $description->setDescription("Test description");
        $description->setCity($this->mockCity(1));
        $description->setEscape($this->mockEscape(1));
        $description->setEntreprise($this->mockEntreprise(1));

        return $description;
    }

    public function assertHasErrors(Description $description, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($description);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidDescription(): void
    {
        $this->assertHasErrors($this->getDescription(), 0);
    }

    public function testValidGetterDescription(): void
    {
        $description = $this->getDescription();
        $this->assertTrue($description->getDescription() === "Test description");
    }

    public function testValidGetterId(): void
    {
        $description = $this->getDescription();
        $this->assertNull($description->getId());
    }

    public function testInvalidBlanckDescription(): void
    {
        $description = $this->getDescription();
        $description->setDescription("");
        $this->assertHasErrors($description, 1);
    }

    public function testValidGetCity(): void
    {
        $description = $this->getDescription();
        $this->assertEquals($description->getCity(), $this->mockCity(1));
    }

    public function testValidGetEntreprise(): void
    {
        $description = $this->getDescription();
        $this->assertEquals($description->getEntreprise(), $this->mockEntreprise(1));
    }

    public function testValidGetEscape(): void
    {
        $description = $this->getDescription();
        $this->assertEquals($description->getEscape(), $this->mockEscape(1));
    }
}
