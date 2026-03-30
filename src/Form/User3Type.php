<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;
class User3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Admin' => "ROLE_ADMIN",
                'User' => "ROLE_USER",
            ],
            'expanded' => true,  
           
        ])
        
        
        ->add('nom', TextType::class, [
            'required' => false,
               'empty_data' => ''
        ])  
            ->add('prenom')
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                
                 'empty_data' => null
                
            ])
            ->add('email') 
            ->add('localisation')
            ->add('image', FileType::class, [
                'label' => 'Choisir une image',     
                'required' => false,
                'mapped' => false, 
                
                
            ])
          
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mot de passe',
              
                ],
                'label' => false, 
            ])
            ->add('confirm_password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Confirm Mot de pass',
                  
                ],
                'label' => false, 
            ])
            ->add('num_telephone')
            ->add('save', SubmitType::class ,[
                'label' => 'Ajouter User'   
               ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
