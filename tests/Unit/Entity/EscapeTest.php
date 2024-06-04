<?php

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\Description;
use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\Grade;
use App\Entity\Link;
use App\Entity\ListDone;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EscapeTest extends KernelTestCase
{
    public function mockListDone(int $id): ListDone
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(ListDone::class, $id);
    }

    public function mockListToDo(int $id): ListToDo
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(ListToDo::class, $id);
    }

    public function mockListFavori(int $id): ListFavori
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(ListFavori::class, $id);
    }

    public function mockGrade(int $id): Grade
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Grade::class, $id);
    }

    public function mockDescription(int $id): Description
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Description::class, $id);
    }

    public function mockLink(int $id): Link
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Link::class, $id);
    }

    public function mockCity(int $id): City
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(City::class, $id);
    }

    public function mockEntreprise(int $id): Entreprise
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Entreprise::class, $id);
    }

    public function mockTag(int $id): Tag
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Tag::class, $id);
    }

    public function getEscape(): Escape
    {
        $escape = new Escape();
        $escape->setName("Test escape");
        $escape->setTime(60);
        $escape->setMinPlayer(2);
        $escape->setMaxPlayer(6);
        $escape->setLevel(3);
        $escape->setPrice(25);
        $escape->setAge(16);
        $escape->setActual(true);
        $escape->addCity($this->mockCity(1));
        $escape->addCity($this->mockCity(2));
        $escape->addEntreprise($this->mockEntreprise(1));
        $escape->addEntreprise($this->mockEntreprise(2));
        $escape->addTag($this->mockTag(1));
        $escape->addTag($this->mockTag(2));
        $escape->addDescription($this->mockDescription(1));
        $escape->addDescription($this->mockDescription(2));
        $escape->addLink($this->mockLink(1));
        $escape->addLink($this->mockLink(2));
        $escape->addGrade($this->mockGrade(1));
        $escape->addGrade($this->mockGrade(2));
        $escape->addListDone($this->mockListDone(1));
        $escape->addListDone($this->mockListDone(2));
        $escape->addListToDo($this->mockListToDo(1));
        $escape->addListToDo($this->mockListToDo(2));
        $escape->addListFavori($this->mockListFavori(1));
        $escape->addListFavori($this->mockListFavori(2));

        return $escape;
    }

    public function assertHasErrors(Escape $escape, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($escape);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValid(): void
    {
        $this->assertHasErrors($this->getEscape(), 0);
    }

    public function testValidGetterId(): void
    {
        $escape = $this->getEscape();
        $this->assertNull($escape->getId());
    }

    public function testInvalidBlanckName(): void
    {
        $escape = $this->getEscape();
        $escape->setName("");
        $this->assertHasErrors($escape, 1);
    }

    public function testValidGetName(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getName(), "Test escape");
    }

    public function testValidGetTime(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getTime(), 60);
    }

    public function testValidGetMinPlayer(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getMinPlayer(), 2);
    }

    public function testValidGetMaxPlayer(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getMaxPlayer(), 6);
    }

    public function testValidGetLevel(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getLevel(), 3);
    }

    public function testValidGetPrice(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getPrice(), 25);
    }

    public function testValidGetAge(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->getAge(), 16);
    }

    public function testValidGetActual(): void
    {
        $escape = $this->getEscape();
        $this->assertEquals($escape->isActual(), true);
    }

    public function testValidGetEntreprises(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getEntreprises());
    }

    public function testValidGetCities(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getCities());
    }

    public function testValidGetTags(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getTags());
    }

    public function testValidRemoveEntreprise(): void
    {
        $escape = $this->getEscape();
        $escape->removeEntreprise($this->mockEntreprise(2));
        $this->assertCount(1, $escape->getEntreprises());
    }

    public function testValidRemoveCity(): void
    {
        $escape = $this->getEscape();
        $escape->removeCity($this->mockCity(2));
        $this->assertCount(1, $escape->getCities());
    }

    public function testValidRemoveTag(): void
    {
        $escape = $this->getEscape();
        $escape->removeTag($this->mockTag(2));
        $this->assertCount(1, $escape->getTags());
    }

    public function testValidGetDescriptions(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getDescriptions());
    }

    public function testValidRemoveDescription(): void
    {
        $escape = $this->getEscape();
        $escape->removeDescription($this->mockDescription(2));
        $this->assertCount(1, $escape->getDescriptions());
    }

    public function testValidGetLinks(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getLinks());
    }

    public function testValidRemoveLink(): void
    {
        $escape = $this->getEscape();
        $escape->removeLink($this->mockLink(2));
        $this->assertCount(1, $escape->getLinks());
    }

    public function testValidGetGrades(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getGrades());
    }

    public function testValidRemoveGrade(): void
    {
        $escape = $this->getEscape();
        $escape->removeGrade($this->mockGrade(2));
        $this->assertCount(1, $escape->getGrades());
    }

    public function testValidGetListToDos(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getListToDos());
    }

    public function testValidRemoveListToDo(): void
    {
        $escape = $this->getEscape();
        $escape->removeListToDo($this->mockListToDo(2));
        $this->assertCount(1, $escape->getListToDos());
    }

    public function testValidGetListDones(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getListDones());
    }

    public function testValidRemoveListDone(): void
    {
        $escape = $this->getEscape();
        $escape->removeListDone($this->mockListDone(2));
        $this->assertCount(1, $escape->getListDones());
    }

    public function testValidGetListFavoris(): void
    {
        $escape = $this->getEscape();
        $this->assertCount(2, $escape->getListFavoris());
    }

    public function testValidRemoveListFavori(): void
    {
        $escape = $this->getEscape();
        $escape->removeListFavori($this->mockListFavori(2));
        $this->assertCount(1, $escape->getListFavoris());
    }
}
