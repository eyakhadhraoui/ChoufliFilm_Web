<?php

namespace App\Form;

use App\Entity\Association;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class AssociationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'required' => false,
               'empty_data' => ''
        ])
        
            ->add('mail_association', TextType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('num_tel', IntegerType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('Description', TextType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('image', FileType::class, [
            
                'label' => 'chisir une image',
           
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
                'label' => 'Ajouter Association'  
               ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Association::class,
        ]);
    }
}
