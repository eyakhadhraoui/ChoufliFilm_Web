<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'required' => false,
            'empty_data' => '',
        ])
        
            ->add('prenom', TextType::class, [
                'required' => false,
            ])
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'empty_data' => null,
              
            ])
            
            ->add('email', TextType::class, [
                'required' => false,
            ])
           
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
                'empty_data' => '',
                'required' => false,
                'label' => false, 
            ])
            ->add('confirm_password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mot de passe',
                   
                  
                ],
                'empty_data' => '',
                'label' => false, 
                'required' => false,
            ])
                ->add('num_telephone', IntegerType::class, [
                    'required' => false,
                ])  
            
                ->add('captcha', Recaptcha3Type::class, [
                    'constraints' => new Recaptcha3(),
                    'action_name' => 'inscription',
                    ])
           
            
            ->add('save', SubmitType::class ,[
                'label' => 'Login'   
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
