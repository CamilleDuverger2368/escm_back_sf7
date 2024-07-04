<?php

namespace App\Form;

use App\Entity\Achievement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AchievementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class)
            ->add("conditionType", ChoiceType::class, [
                "choices" => [
                    "escape" => "escape",
                    "social" => "social"
                ]
            ])
            ->add("tropheeType", ChoiceType::class, [
                "choices" => [
                    "3D" => "3D",
                    "image" => "image",
                    "title" => "title"
                ]
            ])
            ->add("trophee", TextType::class)
            ->add("scalable", CheckboxType::class, [
                "required" => false,
            ])
            ->add("previousStep", EntityType::class, [
                "class" => Achievement::class,
                "choice_label" => "name",
                "multiple" => false,
                "expanded" => false,
                "required" => false
            ])
            ->add("nextStep", EntityType::class, [
                "class" => Achievement::class,
                "choice_label" => "name",
                "multiple" => false,
                "expanded" => false,
                "required" => false
            ])
            ->add("description", TextareaType::class)
            ->add("checker", TextType::class)
            ->add("enregistrer", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Achievement::class,
        ]);
    }
}
