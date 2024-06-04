<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Entreprise;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FindEscapeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, [
                "required" => false
            ])
            ->add("cities", EntityType::class, [
                "class" => City::class,
                "choice_label" => "name",
                "multiple" => true,
                "expanded" => false,
                "required" => false
            ])
            ->add("entreprises", EntityType::class, [
                "class" => Entreprise::class,
                "choice_label" => "name",
                "multiple" => true,
                "expanded" => false,
                "required" => false
            ])
            ->add("search", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
