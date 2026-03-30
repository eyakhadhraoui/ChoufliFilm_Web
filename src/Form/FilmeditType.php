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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FilmeditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('directeur', TextType::class, [
                'required' => false,
                   'empty_data' => ''
            ])
            ->add('note', IntegerType::class, [
                'required' => false,
                   'empty_data' => 0.0
            ])
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
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir un genre.']),
                ],
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description ne peut pas être vide.']),
                ],
                  'empty_data' => ''
            ])
            ->add('date_debut', DateType::class, [
                'widget'      => 'single_text',
                'required'    => false,
                'data'        => new \DateTime(),
                'empty_data'  => null,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir une date de début.']),
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
                    new Assert\NotBlank(['message' => 'Veuillez choisir une date de fin.']),
                    new Assert\Type([
                        'type'    => \DateTimeInterface::class,
                        'message' => 'Veuillez saisir une date valide.',
                    ]),
                ],
            ])
            ->add('duree', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir la durée du film.']),
                ],
                'empty_data' => 0
            ])
            ->add('image_film', FileType::class, [
                'label' => 'Choisir une image',
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/png', 'image/jpeg'],
                        'mimeTypesMessage' => 'Seuls les fichiers PNG ou JPG sont autorisés.',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier Film'
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
            'constraints' => [
                new Assert\Callback(function ($data, ExecutionContextInterface $context) {
                    // $data est une instance de Film
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
