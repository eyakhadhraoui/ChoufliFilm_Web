<?php

namespace App\Form;

use App\Entity\Film;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FilmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('directeur')
            ->add('note')
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Horror'      => 'Horror',
                    'Comedy'      => 'Comedy',
                    'Drama'       => 'Drama',
                    'Romance'     => 'Romance',
                    'Thriller'    => 'Thriller',
                    'Action'      => 'Action',
                    'Adventure'   => 'Adventure',
                    'Documentary' => 'Documentary',
                    'Crime'       => 'Crime',
                ],
                'expanded'    => false,
                'attr'        => [
                    'class' => 'form-control',
                ]
            ])
            ->add('description')
            ->add('date_debut', DateType::class, [
                'widget'      => 'single_text',
                'required'    => false,
                'data'        => new \DateTime(), // Valeur par défaut : aujourd'hui
                'empty_data'  => null,
                'constraints' => [
                    new Assert\Type([
                        'type'    => \DateTimeInterface::class,
                        'message' => 'Veuillez saisir une date valide.',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value'   => new \DateTime('today'),
                        'message' => 'La date ne peut pas être antérieure à aujourd\'hui.',
                    ]),
                ],
            ])
            ->add('date_fin', DateType::class, [
                'widget'      => 'single_text',
                'required'    => false,
                'empty_data'  => null,
                'constraints' => [
                    new Assert\Type([
                        'type'    => \DateTimeInterface::class,
                        'message' => 'Veuillez saisir une date valide.',
                    ]),
                    // La contrainte de comparaison avec date_debut est gérée dans le Callback
                ],
            ])
            ->add('duree')
            ->add('image_film', FileType::class, [
                'label' => 'Choisir une image',
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
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer La réclamation'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
            'constraints' => [
                new Assert\Callback(function ($data, ExecutionContextInterface $context) {
                    
                    if (null === $data) {
                        return;
                    }

                    // On utilise les getters de l'entité Film
                    $dateDebut = $data->getDateDebut();
                    $dateFin   = $data->getDateFin();

                    // Si une des dates est manquante, on ne valide pas la cohérence
                    if (!$dateDebut instanceof \DateTimeInterface || !$dateFin instanceof \DateTimeInterface) {
                        return;
                    }

                    // Vérifier que la date de fin est strictement supérieure à la date de début
                    if ($dateFin <= $dateDebut) {
                        $context->buildViolation('La date de fin doit être postérieure à la date de début.')
                            ->atPath('date_fin')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
