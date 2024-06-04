<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Entreprise;
use App\Entity\Link;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("link", TextType::class)
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
            "data_class" => Link::class,
        ]);
    }
}
