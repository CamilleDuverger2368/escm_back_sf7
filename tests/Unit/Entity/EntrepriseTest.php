<?php

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\Entreprise;
use App\Entity\Escape;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntrepriseTest extends KernelTestCase
{
    public function mockEscape(int $id): Escape
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Escape::class, $id);
    }

    public function mockCity(int $id): City
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(City::class, $id);
    }

    public function getEntreprise(): Entreprise
    {
        $entreprise = new Entreprise();
        $entreprise->setName("Test entreprise");
        $entreprise->addCity($this->mockCity(1));
        $entreprise->addCity($this->mockCity(2));
        $entreprise->addEscape($this->mockEscape(1));
        $entreprise->addEscape($this->mockEscape(2));

        return $entreprise;
    }

    public function assertHasErrors(Entreprise $entreprise, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($entreprise);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidSetterName(): void
    {
        $this->assertHasErrors($this->getEntreprise(), 0);
    }

    public function testValidGetterName(): void
    {
        $entreprise = $this->getEntreprise();
        $this->assertTrue($entreprise->getName() === "Test entreprise");
    }

    public function testValidGetterId(): void
    {
        $entreprise = $this->getEntreprise();
        $this->assertNull($entreprise->getId());
    }

    public function testInvalidBlanckName(): void
    {
        $entreprise = $this->getEntreprise();
        $entreprise->setName("");
        $this->assertHasErrors($entreprise, 1);
    }

    public function testValidGetCities(): void
    {
        $entreprise = $this->getEntreprise();
        $this->assertCount(2, $entreprise->getCities());
    }

    public function testValidGetEscapes(): void
    {
        $entreprise = $this->getEntreprise();
        $this->assertCount(2, $entreprise->getEscapes());
    }

    public function testValidRemoveCity(): void
    {
        $entreprise = $this->getEntreprise();
        $entreprise->removeCity($this->mockCity(1));
        $this->assertCount(1, $entreprise->getCities());
    }

    public function testValidRemoveEscape(): void
    {
        $entreprise = $this->getEntreprise();
        $entreprise->removeEscape($this->mockEscape(1));
        $this->assertCount(1, $entreprise->getEscapes());
    }

    public function testValidClearCities(): void
    {
        $entreprise = $this->getEntreprise();
        $entreprise->clearCities();
        $this->assertCount(0, $entreprise->getCities());
    }

    public function testValidClearEscape(): void
    {
        $entreprise = $this->getEntreprise();
        $entreprise->clearEscapes();
        $this->assertCount(0, $entreprise->getEscapes());
    }
}
