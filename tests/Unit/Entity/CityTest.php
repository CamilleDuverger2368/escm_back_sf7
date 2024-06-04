<?php

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CityTest extends KernelTestCase
{
    public function getCity(): City
    {
        return (new City())->setName("Test");
    }

    public function assertHasErrors(City $city, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($city);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidSetterName(): void
    {
        $this->assertHasErrors($this->getCity(), 0);
    }

    public function testValidGetterName(): void
    {
        $city = $this->getCity();
        $this->assertTrue($city->getName() === "Test");
    }

    public function testValidGetterId(): void
    {
        $city = $this->getCity();
        $this->assertNull($city->getId());
    }

    public function testInvalidBlanckName(): void
    {
        $city = $this->getCity();
        $city->setName("");
        $this->assertHasErrors($city, 2);
    }

    public function testInvalidNumberInName(): void
    {
        $city = $this->getCity();
        $city->setName("213412");
        $this->assertHasErrors($city, 1);
    }

    public function testInvalidTooLongName(): void
    {
        $city = $this->getCity();
        $city->setName("qrwetyiuqwreyiutwqreuyitwreqyutirqweuyitrwequy
        tirwequytirwequyitrweqtyiurweqyiutrweqiyturweq
        yuitrwequyitrewqiutyrweqiyturweqiuytrweqyiutrw
        qeyiturqweyiutrweqyiutrwqeiyutrwequyitrwqeyuit
        rwequyitrwqeyiutrwqeyuitrweqyiutrwequyitrqweuy
        itrwqeyiutrwqeyiutrwequiytrwequyitrweqiuytrweq
        uyitrwequyitrwqeuyitrwqeiyut");
        $this->assertHasErrors($city, 1);
    }
}
