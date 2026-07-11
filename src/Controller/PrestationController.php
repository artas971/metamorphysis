<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Repository\PrestationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrestationController extends AbstractController
{
    // 1. La route pour afficher TOUTES les prestations (tes cartes)
    #[Route('/prestations', name: 'app_prestation_index')]
        public function index(PrestationRepository $prestationRepository): Response
        {
            return $this->render('prestation/index.html.twig', [
                // On trie d'abord par 'estMisEnAvant' (les TRUE d'abord), puis par 'ordre' (du plus petit au plus grand)
                'prestations' => $prestationRepository->findBy([], [
                    'estMisEnAvant' => 'DESC',
                    'ordre' => 'ASC'
                ]),
            ]);
        }

    // 2. La route pour afficher UNE SEULE prestation en détail
    // On utilise {id} car c'est ce qui existe dans ton entité
    #[Route('/prestation/{id}', name: 'app_prestation_show')]
        public function show(Prestation $prestation): Response
        {
            // Symfony est intelligent : grâce à l'ID dans l'URL, 
            // il va chercher automatiquement la bonne prestation en base de données !
            
            return $this->render('prestation/show.html.twig', [
                'prestation' => $prestation,
            ]);
        }
    
}