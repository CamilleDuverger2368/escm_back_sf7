<?php

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\User;
use App\Entity\ListDone;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Entity\Room;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function mockRoom(int $id): Room
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(Room::class, $id);
    }

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

    public function mockCity(int $id): City
    {
        return static::getContainer()->get("doctrine.orm.entity_manager")->find(City::class, $id);
    }

    public function getUser(): User
    {
        $user = new User();
        $user->setEmail("test@mail.com");
        $user->setRoles(["ROLE"]);
        $user->setPassword("Qwerty1234!");
        $user->setFirstname("Test");
        $user->setName("Test");
        $user->setPseudo("Test");
        $user->setAge(25);
        $user->setLevel(2.0);
        $user->setGrade(2);
        $user->setPronouns("They");
        $user->setProfil("Solver");
        $user->setCity($this->mockCity(1));
        $user->setValidated(true);
        $user->setLink("Test");
        $user->addListDone($this->mockListDone(1));
        $user->addListDone($this->mockListDone(2));
        $user->addListToDo($this->mockListToDo(1));
        $user->addListToDo($this->mockListToDo(2));
        $user->addListFavori($this->mockListFavori(1));
        $user->addListFavori($this->mockListFavori(2));
        $user->addRoom($this->mockRoom(3));
        $user->addRoom($this->mockRoom(2));
        return $user;
    }

    public function assertHasErrors(User $user, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($user);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValid(): void
    {
        $this->assertHasErrors($this->getUser(), 0);
    }

    public function testValidGetLink(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getLink(), "Test");
    }

    public function testValidIsValidated(): void
    {
        $user = $this->getUser();
        $this->assertTrue($user->isValidated());
    }

    public function testValidGetCity(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getCity(), $this->mockCity(1));
    }

    public function testInvalidProfil(): void
    {
        $user = $this->getUser();
        $user->setEmail("Solver3");
        $this->assertHasErrors($user, 1);
    }

    public function testValidGetProfil(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getProfil(), "Solver");
    }

    public function testInvalidPronouns(): void
    {
        $user = $this->getUser();
        $user->setEmail("they3");
        $this->assertHasErrors($user, 1);
    }

    public function testValidGetPronouns(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getPronouns(), "They");
    }

    public function testValidGetGrade(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getGrade(), 2);
    }

    public function testValidGetLevel(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getLevel(), 2.0);
    }

    public function testValidGetterId(): void
    {
        $user = $this->getUser();
        $this->assertNull($user->getId());
    }

    public function testInvalidBlanckEmail(): void
    {
        $user = $this->getUser();
        $user->setEmail("");
        $this->assertHasErrors($user, 1);
    }

    public function testValidGetEmail(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getEmail(), "test@mail.com");
    }

    public function testValidGetRoles(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getRoles(), ["ROLE", "ROLE_USER"]);
    }

    public function testInvalidBlanckPassword(): void
    {
        $user = $this->getUser();
        $user->setPassword("");
        $this->assertHasErrors($user, 1);
    }

    public function testInvalidPassword(): void
    {
        $user = $this->getUser();
        $user->setPassword("qwerty");
        $this->assertHasErrors($user, 1);
    }

    public function testValidGetUserIdentifier(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getUserIdentifier(), "test@mail.com");
    }

    public function testValidGetUserName(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getUsername(), "test@mail.com");
    }

    public function testValidGetPassword(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getPassword(), "Qwerty1234!");
    }

    public function testValidEraseCredentials(): void
    {
        $user = $this->getUser();
        $user->eraseCredentials();
        $this->assertEquals($user, $this->getUser());
    }

    public function testInvalidNumberName(): void
    {
        $user = $this->getUser();
        $user->setName("qwerty1234455");
        $this->assertHasErrors($user, 1);
    }

    public function testInvalidTooLongName(): void
    {
        $user = $this->getUser();
        $user->setName("qrwetyiuqwreyiutwqreuyitwreqyutirqweuyitrwequy
        tirwequytirwequyitrweqtyiurweqyiutrweqiyturweq
        yuitrwequyitrewqiutyrweqiyturweqiuytrweqyiutrw
        qeyiturqweyiutrweqyiutrwqeiyutrwequyitrwqeyuit
        rwequyitrwqeyiutrwqeyuitrweqyiutrwequyitrqweuy
        itrwqeyiutrwqeyiutrwequiytrwequyitrweqiuytrweq
        uyitrwequyitrwqeuyitrwqeiyut");
        $this->assertHasErrors($user, 1);
    }

    public function testInvalidBlanckName(): void
    {
        $user = $this->getUser();
        $user->setName("");
        $this->assertHasErrors($user, 2);
    }


    public function testInvalidNumberFirstname(): void
    {
        $user = $this->getUser();
        $user->setFirstname("qwerty1234455");
        $this->assertHasErrors($user, 1);
    }

    public function testInvalidTooLongFirstname(): void
    {
        $user = $this->getUser();
        $user->setFirstname("qrwetyiuqwreyiutwqreuyitwreqyutirqweuyitrwequy
        tirwequytirwequyitrweqtyiurweqyiutrweqiyturweq
        yuitrwequyitrewqiutyrweqiyturweqiuytrweqyiutrw
        qeyiturqweyiutrweqyiutrwqeiyutrwequyitrwqeyuit
        rwequyitrwqeyiutrwqeyuitrweqyiutrwequyitrqweuy
        itrwqeyiutrwqeyiutrwequiytrwequyitrweqiuytrweq
        uyitrwequyitrwqeuyitrwqeiyut");
        $this->assertHasErrors($user, 1);
    }

    public function testInvalidBlanckFirstname(): void
    {
        $user = $this->getUser();
        $user->setFirstname("");
        $this->assertHasErrors($user, 2);
    }

    public function testValidGetName(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getName(), "Test");
    }

    public function testValidGetFirstname(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getFirstname(), "Test");
    }

    public function testValidGetPseudo(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getPseudo(), "Test");
    }

    public function testValidGetAge(): void
    {
        $user = $this->getUser();
        $this->assertEquals($user->getAge(), 25);
    }

    public function testValidGetListToDos(): void
    {
        $user = $this->getUser();
        $this->assertCount(2, $user->getListToDos());
    }

    public function testValidRemoveListToDo(): void
    {
        $user = $this->getUser();
        $user->removeListToDo($this->mockListToDo(2));
        $this->assertCount(1, $user->getListToDos());
    }

    public function testValidGetListDones(): void
    {
        $user = $this->getUser();
        $this->assertCount(2, $user->getListDones());
    }

    public function testValidRemoveListDone(): void
    {
        $user = $this->getUser();
        $user->removeListDone($this->mockListDone(2));
        $this->assertCount(1, $user->getListDones());
    }

    public function testValidGetListFavoris(): void
    {
        $user = $this->getUser();
        $this->assertCount(2, $user->getListFavoris());
    }

    public function testValidRemoveListFavori(): void
    {
        $user = $this->getUser();
        $user->removeListFavori($this->mockListFavori(2));
        $this->assertCount(1, $user->getListFavoris());
    }

    public function testValidGetRooms(): void
    {
        $user = $this->getUser();
        $this->assertCount(2, $user->getRooms());
    }

    public function testValidRemoveRoom(): void
    {
        $user = $this->getUser();
        $user->removeRoom($this->mockRoom(2));
        $this->assertCount(1, $user->getRooms());
    }
}
