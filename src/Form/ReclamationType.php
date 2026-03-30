<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
        ->add('Description', TextType::class, [
            'required' => false,
        ])
        ->add('type', ChoiceType::class, [
            'choices' => [
                'Type Technique' => 'Type Technique',
                'Type lié de Reservation' => 'Type lié de Reservation',
                'Type lié de Paiement' => 'Type lié de Paiement',
                'Autre' => 'Autre',
            ],
            'placeholder' => 'Choose a type', 
            'expanded' => false,
            'multiple' => false, 
            'required' => false, // Géré par la validation
            'attr' => [
                'class' => 'form-control', 
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Le champ Type est Obligatoire.',
                ]),
            ],
        ])
      
        ->add('image', FileType::class, [
            
                'label' => 'chisir une image',
                'required' => false, 
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/png', 'image/jpeg'],
                        'mimeTypesMessage' => 'Seuls les fichiers PNG ou JPG sont autorisés.',
                    ]),
                    new Assert\NotBlank([
                        'message' => 'Veuillez télécharger une image.',
                    ]),
                ], 
        ])                          
      
         
            ->add('save', SubmitType::class ,[
                'label' => 'Envoyer La réclamation'  
               ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}


