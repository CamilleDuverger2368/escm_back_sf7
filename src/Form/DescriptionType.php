<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Description;
use App\Entity\Entreprise;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("description", TextareaType::class)
            ->add("city", EntityType::class, [
                "class" => City::class,
                "choice_label" => "name",
                "expanded" => true
            ])
            ->add("entreprise", EntityType::class, [
                "class" => Entreprise::class,
                "choice_label" => "name",
                "expanded" => true
            ])
            ->add("enregistrer", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Description::class,
        ]);
    }
}
