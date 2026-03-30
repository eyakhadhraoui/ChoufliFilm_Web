<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class ReponseautreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reponse', ChoiceType::class, [
                'choices' => [
                    'Nous prenons en compte votre demande.' => 'Nous prenons en compte votre demande.',
                    'Désolé pour ce désagrément, nous agissons.' => 'Désolé pour ce désagrément, nous agissons.',
                    'Merci de votre retour, nous allons vérifier.' => 'Merci de votre retour, nous allons vérifier.',
                    'Autre' => 'Autre',
                ],
                'expanded' => false,
                'multiple' => false, 
                'required' => true,
               
                'placeholder' => 'Choisissez une réponse',
                'attr' => [
                    'class' => 'form-control', 
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Répondre'  
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}