<?php

namespace App\DataFixtures;

use App\Entity\Prestation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. On prépare notre liste de prestations
        $prestations = [
            [
                'nom' => 'Consultation Initiale',
                'description' => 'Un premier diagnostic complet pour cibler vos besoins et définir une stratégie d\'évolution.',
                'prix' => 50,
                'duree' => 45
            ],
            [
                'nom' => 'Suivi Mensuel',
                'description' => 'Un accompagnement régulier avec des points d\'étape hebdomadaires et des ajustements techniques.',
                'prix' => 150,
                'duree' => 120
            ],
            [
                'nom' => 'Pack Métamorphose',
                'description' => 'La refonte totale de votre approche. Inclut un suivi intensif et la création d\'assets dédiés.',
                'prix' => 450,
                'duree' => 300
            ]
        ];

        // 2. On boucle sur la liste pour créer les entités
        foreach ($prestations as $data) {
            $prestation = new Prestation();
            $prestation->setNom($data['nom'])
                       ->setDescription($data['description'])
                       ->setPrix($data['prix'])
                       ->setDuree($data['duree']);

            // On dit à Doctrine (le gestionnaire de base de données) de garder ça en mémoire
            $manager->persist($prestation);
        }

        // 3. On envoie tout dans la base de données d'un seul coup !
        $manager->flush();
    }
}