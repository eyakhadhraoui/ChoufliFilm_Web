<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Salle;
use App\Entity\Association;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateReservation', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a date'
                    ]),
                    new Assert\Callback([$this, 'validateReservationDate'])
                ]
            ])
            ->add('type_Place', ChoiceType::class, [
                'choices' => [
                    'Regular' => 'Regular',
                    'VIP' => 'VIP'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a seat type'
                    ])
                ]
            ])
            ->add('nombre_places', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter the number of seats'
                    ]),
                  
                ]
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nomSalle',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a cinema'
                    ])
                ]
            ])
            ->add('association', EntityType::class, [
                'class' => Association::class,
                'choice_label' => 'nom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select an association'
                    ])
                ]
            ])
            ->add('titre', HiddenType::class)
            ->add('save', SubmitType::class, ['label' => 'Add to Cart'])
            ->add('pay', SubmitType::class, ['label' => 'Pay Now']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }

    public function validateReservationDate($date, ExecutionContextInterface $context): void
    {
        if (!$date) {
            return;
        }

        $reservation = $context->getRoot()->getData();
        $film = $reservation->getFilm();

        if (!$film) {
            return;
        }

        $today = new \DateTime('today');
        $dateDebut = $film->getDateDebut();
        $dateFin = $film->getDateFin();

        if ($date < $today) {
            $context->buildViolation('Please select a future date')
                ->atPath('dateReservation')
                ->addViolation();
        }

        if ($date < $dateDebut) {
            $context->buildViolation('Reservations are not yet open for this date. Available from {{ date }}')
                ->setParameter('{{ date }}', $dateDebut->format('Y-m-d'))
                ->atPath('dateReservation')
                ->addViolation();
        }

        if ($date > $dateFin) {
            $context->buildViolation('Reservations are closed for this date. Last available date is {{ date }}')
                ->setParameter('{{ date }}', $dateFin->format('Y-m-d'))
                ->atPath('dateReservation')
                ->addViolation();
        }
    }
}
