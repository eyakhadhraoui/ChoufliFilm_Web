<?php

namespace App\Form;

use App\Entity\Association;
use App\Entity\Ressource;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class RessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           
                ->add('besoin_specifique', ChoiceType::class, [
                    'choices' => [
                        '10 DT to buy food' => '10 DT to buy food',
                        '15 DT to buy clothes' => '15 DT to buy clothes',
                        '20 DT to buy medicines' => '20 DT to buy medicines',
                    ],
                    'placeholder' => 'Choose a Specific Needs', 
                    'expanded' => false,
                    'multiple' => false, 
                    'attr' => [
                        'class' => 'form-control', 
                    ]
                ])
            
            
            ->add('save', SubmitType::class ,[
                'label' => 'Envoyer La réclamation'  
               ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ressource::class,
        ]);
    }
}
