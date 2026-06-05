<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Ex: Jean Dupont',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'attr' => [
                    'placeholder' => 'Ex: jean@exemple.com',
                ]
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Sujet de votre demande',
                'required' => false, // On rend ce champ facultatif
                'attr' => [
                    'placeholder' => 'Ex: Demande de devis',
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'rows' => 6, // Pour avoir une zone de texte assez grande
                    'placeholder' => 'Détaillez votre projet ou votre question ici...',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas d'entité liée, donc on laisse vide
        ]);
    }
}