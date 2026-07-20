<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRendezVous', null, [
                'widget' => 'single_text',
                'label' => 'Choisissez la date et l\'heure de votre rendez-vous',
                'attr' => ['class' => 'form-control bg-dark text-white border-warning']
            ])
            // On a supprimé 'statut', 'user', et 'prestation' car on va les remplir automatiquement !
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => \App\Entity\Seance::class,            
        ]);
    }
}
