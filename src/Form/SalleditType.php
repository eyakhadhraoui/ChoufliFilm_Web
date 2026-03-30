<?php

namespace App\Form;

use App\Entity\Film;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
class SalleditType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_salle', TextType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('nbr_places', IntegerType::class, [
                'required' => false,
                   'empty_data' => 0
            ])
            ->add('Type_salle', ChoiceType::class, [
                'choices' => [
                  'standard' => 'standard',
                    'Premium' => 'Premium',
                    'economic' => 'economic',
                ],
              
                'expanded' => false,
                'multiple' => false, 
                'attr' => [
                    'class' => 'form-control', 
                ]
            ])
            ->add('Etat_salle', ChoiceType::class, [
                'choices' => [
                    'Ouverte' => 'Ouverte',
                    'Fermée' => 'Fermée',
                ],
               
                'expanded' => false,
                'multiple' => false, 
                'attr' => [
                    'class' => 'form-control', 
                ]
            ])
            ->add('films', EntityType::class, [
                'class' => Film::class,
                'choice_label' => 'titre',
                'multiple' => true,
            ])
            ->add('image_salle', FileType::class, [
            
                'label' => 'chisir une image',
           
                'mapped' => false, 
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/png', 'image/jpeg'],
                        'mimeTypesMessage' => 'Seuls les fichiers PNG ou JPG sont autorisés.',
                    ]),
                ],
              ])      
         
            ->add('save', SubmitType::class ,[
                'label' => 'Envoyer La réclamation'  
               ])
            ->add('save', SubmitType::class ,[
                'label' => 'Ajouter Salle'  
               ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
        ]);
    }
}
