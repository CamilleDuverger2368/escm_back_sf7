<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TagTest extends KernelTestCase
{
    public function getTag(): Tag
    {
        return (new Tag())->setName("Test");
    }

    public function assertHasErrors(Tag $tag, int $nb = 0): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get("validator")->validate($tag);
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($nb, $errors, implode(", ", $messages));
    }

    public function testValidSetterName(): void
    {
        $this->assertHasErrors($this->getTag(), 0);
    }

    public function testValidGetterName(): void
    {
        $tag = $this->getTag();
        $this->assertTrue($tag->getName() === "Test");
    }

    public function testValidGetterId(): void
    {
        $tag = $this->getTag();
        $this->assertNull($tag->getId());
    }

    public function testInvalidBlanckName(): void
    {
        $tag = $this->getTag();
        $tag->setName("");
        $this->assertHasErrors($tag, 1);
    }
}
