<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType as IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EscapeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class)
            ->add("actual", CheckboxType::class, [
                "required" => false,
            ])
            ->add("time", IntegerType::class)
            ->add("minPlayer", IntegerType::class)
            ->add("maxPlayer", IntegerType::class)
            ->add("price", IntegerType::class)
            ->add("age", IntegerType::class)
            ->add("level", IntegerType::class)
            ->add("cities", EntityType::class, [
                "class" => City::class,
                "choice_label" => "name",
                "multiple" => true,
                "expanded" => true
            ])
            ->add("entreprises", EntityType::class, [
                "class" => Entreprise::class,
                "choice_label" => "name",
                "multiple" => true,
                "expanded" => true
            ])
            ->add("tags", EntityType::class, [
                "class" => Tag::class,
                "choice_label" => "name",
                "multiple" => true,
                "expanded" => true
            ])
            ->add("enregistrer", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Escape::class,
        ]);
    }
}
