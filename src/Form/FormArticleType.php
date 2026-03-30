<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
use Symfony\Component\Validator\Constraints\File;

class FormArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [   
                'label' => 'Title',
                'attr' => ['placeholder' => 'Enter the title of the article'],
                'required' => false,
                'empty_data' => ''
            ])
            ->add('datePublication', DateTimeType::class, [
                'required' => false,
                'empty_data' => null,
                'label' => 'Publication Date',
                'widget' => 'single_text',
                 'empty_data' => null
            ])
            ->add('contenu', TextareaType::class, [
                'required' => false,
                'label' => 'Content',
                'attr' => ['placeholder' => 'Enter the article content'],
                 'empty_data' => ''
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Film' => 'film',
                    'Cinéma' => 'cinema',
                    'Snacks' => 'snacks',
                    'Acteurs' => 'acteurs',
                    'Autre' => 'autre',
                ],
                'required' => false,
                    
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPEG ou PNG uniquement)',
                'mapped' => false, // Car le fichier n'est pas directement stocké dans l'entité
                'required' => false, 
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ])
                ],
            ]);
    } 

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'translation_domain' => false, // Optionnel : éviter la traduction automatique
        ]);
    }
}
