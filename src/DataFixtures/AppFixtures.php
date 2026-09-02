<?php

namespace App\DataFixtures;

use App\Entity\Etape;
use App\Entity\Page;
use App\Entity\Prestation;
use App\Entity\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // =========================================================================
        // 1. PRESTATIONS PAR DÉFAUT
        // =========================================================================
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

        foreach ($prestations as $data) {
            $prestation = new Prestation();
            $prestation->setNom($data['nom'])
                       ->setDescription($data['description'])
                       ->setPrix($data['prix'])
                       ->setDuree($data['duree'])
                       ->setNombreSeances($data['nombreSeances'] ?? 1);
            $manager->persist($prestation);
        }

        // =========================================================================
        // 2. PAGE 1 : MENTIONS LÉGALES & CONFIDENTIALITÉ (/mentions-legales)
        // =========================================================================
        $pageMentions = new Page();
        $pageMentions->setTitre('Mentions Légales & Confidentialité')
                     ->setSlug('mentions-legales')
                     ->setAfficherTitre(true)
                     ->setAfficherMenu(false)
                     ->setAfficherFooter(true)
                     ->setFondBlocsUnifie('individuel')
                     ->setIsPublished(true);
        $manager->persist($pageMentions);

        // Section 1 : Mentions Légales
        $secMentions1 = new Section();
        $secMentions1->setPage($pageMentions)
                     ->setOrdre(1)
                     ->setDisposition('texte_centre')
                     ->setTitre('Mentions Légales')
                     ->setSousTitre('Éditeur du site & Hébergement')
                     ->setCouleurFond('olive')
                     ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(48)
                     ->setMargeHaut(0)->setMargeBas(0)
                     ->setContenu('<h3>1. Éditeur du site</h3><p><strong>METAMORPHYSIS</strong><br>Consultante en relation humaine & Analyse transactionnelle<br>Responsable de la publication : <strong>Louisa CHOUIHI</strong><br>SIRET : En cours d\'immatriculation<br>Contact : contact@metamorphysis.fr</p><h3>2. Hébergement</h3><p>Le site est hébergé par la société <strong>Hostinger International Ltd.</strong><br>61 Lordou Vironos Street, 6023 Larnaca, Chypre<br>Site web : <a href="https://www.hostinger.fr" target="_blank" rel="noopener noreferrer" style="color: var(--meta-gold);">www.hostinger.fr</a></p><h3>3. Propriété intellectuelle</h3><p>L\'ensemble des éléments graphiques, textuels, logos, marques et structures composant ce site sont la propriété exclusive de METAMORPHYSIS et de Louisa CHOUIHI. Toute reproduction, diffusion, modification ou utilisation sans autorisation préalable expresse est strictement interdite.</p>');
        $manager->persist($secMentions1);

        // Section 2 : Politique de Confidentialité (RGPD)
        $secMentions2 = new Section();
        $secMentions2->setPage($pageMentions)
                     ->setOrdre(2)
                     ->setDisposition('texte_centre')
                     ->setTitre('Politique de Confidentialité')
                     ->setSousTitre('Protection de vos données personnelles [RGPD]')
                     ->setCouleurFond('olive')
                     ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(48)
                     ->setMargeHaut(0)->setMargeBas(0)
                     ->setContenu('<h3>1. Données collectées</h3><p>Dans le cadre de la prise de contact, de la gestion de votre compte et de la réservation de séances d\'accompagnement, METAMORPHYSIS est amenée à collecter des informations strictement nécessaires : nom, prénom, adresse e-mail, numéro de téléphone et historique des réservations.</p><h3>2. Finalité des traitements</h3><p>Vos données personnelles sont traitées exclusivement pour assurer la gestion de vos rendez-vous, le suivi de vos accompagnements personnalisés, la facturation et la communication liée à vos séances. Vos données ne sont en aucun cas cédées, louées ou vendues à des tiers.</p><h3>3. Vos droits</h3><p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d\'un droit d\'accès, de rectification, de suppression et de portabilité de vos données. Vous pouvez exercer ces droits à tout moment en écrivant à : <strong>contact@metamorphysis.fr</strong>.</p>');
        $manager->persist($secMentions2);

        // Section 3 : Conditions d'Annulation
        $secMentions3 = new Section();
        $secMentions3->setPage($pageMentions)
                     ->setOrdre(3)
                     ->setDisposition('texte_centre')
                     ->setTitre('Conditions d\'Annulation')
                     ->setSousTitre('Modalités relatives aux rendez-vous')
                     ->setCouleurFond('olive')
                     ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(48)
                     ->setMargeHaut(0)->setMargeBas(0)
                     ->setContenu('<p>Chaque séance d\'accompagnement fait l\'objet d\'une préparation sur-mesure et d\'un créneau horaire strictement réservé à votre attention.</p><p><strong>Délai de prévenance :</strong> Tout report ou annulation de séance doit être formulé au minimum <strong>48 heures à l\'avance</strong> par e-mail ou depuis votre espace personnel.</p><p>En cas d\'annulation tardive (moins de 48h avant le rendez-vous) ou de non-présentation, la séance sera considérée comme due et ne donnera lieu à aucun remboursement, sauf cas de force majeure dûment justifié.</p>');
        $manager->persist($secMentions3);

        // =========================================================================
        // 3. PAGE 2 : À PROPOS (/a-propos)
        // =========================================================================
        $pageAPropos = new Page();
        $pageAPropos->setTitre('À Propos')
                    ->setSlug('a-propos')
                    ->setAfficherTitre(false)
                    ->setAfficherMenu(true)
                    ->setAfficherFooter(true)
                    ->setFondBlocsUnifie('pourpre')
                    ->setOrdreMenu(2)
                    ->setIsPublished(true);
        $manager->persist($pageAPropos);

        $secAPropos = new Section();
        $secAPropos->setPage($pageAPropos)
                   ->setOrdre(1)
                   ->setDisposition('img_droite')
                   ->setTitre('CHOUIHI LOUISA')
                   ->setSousTitre('CONSULTANTE EN RELATION HUMAINE')
                   ->setCouleurFond('plum')
                   ->setMedia('louisa-presentation-6a502f265cd20760387695.jpg')
                   ->setImageCadreCouleur('plum')
                   ->setImageCadreGauche(0)->setImageCadreDroite(35)->setImageCadreHaut(0)->setImageCadreBas(0)
                   ->setImagePosX(0)->setImagePosY(0)
                   ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(0)
                   ->setMargeHaut(0)->setMargeBas(0)
                   ->setCitation('L\'accompagnement Métamorphysis s\'adresse à celles et ceux qui ressentent le besoin d\'éclairer leur trajectoire, de dénouer des blocages profonds et de reprendre les rênes de leur vie avec clarté, authenticité et sérénité.')
                   ->setCitationPosX(25)
                   ->setCitationPosY(-40)
                   ->setCitationLargeur(75)
                   ->setCitationCouleurFond('meta-olive')
                   ->setCitationCouleurTexte('meta-ivory')
                   ->setContenu("Diplômée et passionnée par l'humain, j'accompagne depuis plusieurs années les personnes et les professionnels dans la compréhension de leurs dynamiques intérieures et relationnelles.\n\nÀ travers l'analyse transactionnelle et une écoute bienveillante mais exigeante, nous explorons ensemble ce qui se joue au-delà des apparences pour déconstruire les croyances limitantes et révéler votre véritable potentiel.");
        $manager->persist($secAPropos);

        // =========================================================================
        // 4. PAGE 3 : L'EXPÉRIENCE (/lexperience)
        // =========================================================================
        $pageExperience = new Page();
        $pageExperience->setTitre('L\'Expérience')
                       ->setSlug('lexperience')
                       ->setAfficherTitre(false)
                       ->setAfficherMenu(true)
                       ->setAfficherFooter(true)
                       ->setFondBlocsUnifie('olive')
                       ->setOrdreMenu(1)
                       ->setIsPublished(true);
        $manager->persist($pageExperience);

        // Section 1 : Introduction & Portrait Vase
        $secExp1 = new Section();
        $secExp1->setPage($pageExperience)
                ->setOrdre(1)
                ->setDisposition('img_droite')
                ->setTitre('L\'EXPÉRIENCE')
                ->setSousTitre('- γνῶθι σεαυτόν - CONNAIS-TOI TOI-MÊME')
                ->setCouleurFond('olive')
                ->setMedia('vase-6a8848bee1143198493651.jpg')
                ->setImageCadreCouleur('plum')
                ->setImageCadreGauche(140)->setImageCadreDroite(0)->setImageCadreHaut(0)->setImageCadreBas(0)
                ->setImagePosX(0)->setImagePosY(0)
                ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(0)
                ->setMargeHaut(0)->setMargeBas(0)
                ->setContenu("Grâce à l'analyse transactionnelle, nous mettons en lumière les schémas inconscients qui influencent vos relations, vos choix et votre bien-être.\n\nComprendre. Transformer. Se libérer. Redevenir auteur de sa propre vie.");
        $manager->persist($secExp1);

        // Section 2 : Processus (5 colonnes)
        $secExp2 = new Section();
        $secExp2->setPage($pageExperience)
                ->setOrdre(2)
                ->setDisposition('grille_colonnes')
                ->setTitre('LE PROCESSUS')
                ->setCouleurFond('olive')
                ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(48)
                ->setMargeHaut(0)->setMargeBas(0);
        $manager->persist($secExp2);

        $etapesExp = [
            ['titre' => 'OBSERVER', 'icone' => 'bi-eye', 'texte' => "Prendre conscience\nde ses schémas\net fonctionnements\nrépétitifs."],
            ['titre' => 'COMPRENDRE', 'icone' => 'bi-diagram-3', 'texte' => "Identifier les origines,\nles blessures et les\nmessages cachés\nderrière les comportements."],
            ['titre' => 'CONFRONTER', 'icone' => 'bi-symmetry-vertical', 'texte' => "Accueillir la vérité,\nsortir des mécanismes\nde protection\net d'évitement."],
            ['titre' => 'TRANSFORMER', 'icone' => 'libellule.svg', 'texte' => "Reprogrammer\nde nouveaux choix,\nalignés avec\nqui vous êtes vraiment."],
            ['titre' => 'S\'ALIGNER', 'icone' => 'bi-crosshair', 'texte' => "Vivre en cohérence\navec vos valeurs,\nvos besoins\net vos désirs profonds."]
        ];

        foreach ($etapesExp as $eData) {
            $etape = new Etape();
            $etape->setSection($secExp2)
                  ->setTitre($eData['titre'])
                  ->setIcone($eData['icone'])
                  ->setTexte($eData['texte']);
            $manager->persist($etape);
        }

        // =========================================================================
        // 5. PAGE 4 : À VOUS (/a-qui-sadresse-cette-demarche)
        // =========================================================================
        $pageAVous = new Page();
        $pageAVous->setTitre('À vous')
                  ->setSlug('a-qui-sadresse-cette-demarche')
                  ->setAfficherTitre(false)
                  ->setAfficherMenu(true)
                  ->setAfficherFooter(true)
                  ->setFondBlocsUnifie('pourpre')
                  ->setOrdreMenu(3)
                  ->setIsPublished(true);
        $manager->persist($pageAVous);

        // Section 1 : Introduction avec canapé
        $secAVous1 = new Section();
        $secAVous1->setPage($pageAVous)
                  ->setOrdre(1)
                  ->setDisposition('img_droite')
                  ->setTitre('À QUI S\'ADRESSE CETTE DÉMARCHE')
                  ->setCouleurFond('plum')
                  ->setMedia('canape-taupe-6a7f1ba5a707a642826507.jpg')
                  ->setPaddingHaut(48)->setPaddingBas(48)->setPaddingGauche(48)->setPaddingDroite(48)
                  ->setMargeHaut(0)->setMargeBas(0);
        $manager->persist($secAVous1);

        // Section 2 : Grille 2 Colonnes Comparatives (Fond Olive, collé en bas)
        $secAVous2 = new Section();
        $secAVous2->setPage($pageAVous)
                  ->setOrdre(2)
                  ->setDisposition('grille_colonnes')
                  ->setCouleurFond('olive')
                  ->setPaddingHaut(48)->setPaddingBas(0)->setPaddingGauche(48)->setPaddingDroite(48)
                  ->setMargeHaut(0)->setMargeBas(0);
        $manager->persist($secAVous2);

        $etapesAVous = [
            [
                'titre' => 'CETTE DÉMARCHE PEUT VOUS CORRESPONDRE SI...',
                'icone' => '',
                'texte' => "· Vous ressentez que les mêmes situations se répètent dans votre vie.\n· Vous avez conscience d'un problème récurrent que vous n'arrivez pas à résoudre.\n→ Vous souhaitez comprendre vos schémas relationnels et émotionnels.\n· Vous êtes prêt(e) à un travail de fond et à vous remettre en question.\n→ Vous aspirez à une transformation profonde et durable."
            ],
            [
                'titre' => 'CETTE DÉMARCHE NE VOUS CONVIENDRA PROBABLEMENT PAS SI...',
                'icone' => '',
                'texte' => "· Vous cherchez des solutions rapides ou toutes faites.\n→ Vous attendez que quelqu'un d'autre change à votre place.\n· Vous n'êtes pas prêt(e) à regarder ce qui gêne.\n→ Vous refusez toute remise en question.\n→ Vous préférez rester dans vos certitudes."
            ]
        ];

        foreach ($etapesAVous as $eData) {
            $etape = new Etape();
            $etape->setSection($secAVous2)
                  ->setTitre($eData['titre'])
                  ->setIcone($eData['icone'])
                  ->setTexte($eData['texte']);
            $manager->persist($etape);
        }

        // Section 3 : Bandeau Conclusion Logo M (Collé en haut, fond transparent)
        $secAVous3 = new Section();
        $secAVous3->setPage($pageAVous)
                  ->setOrdre(3)
                  ->setDisposition('bandeau_conclusion')
                  ->setCouleurFond('none')
                  ->setPaddingHaut(0)->setPaddingBas(0)->setPaddingGauche(0)->setPaddingDroite(0)
                  ->setMargeHaut(0)->setMargeBas(0)
                  ->setContenu("Cette démarche demande du courage.\nMais c'est aussi là que tout commence.");
        $manager->persist($secAVous3);

        // Enregistrement final en base de données
        $manager->flush();
    }
}