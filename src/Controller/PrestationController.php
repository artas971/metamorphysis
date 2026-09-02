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
    public function index(PrestationRepository $prestationRepository, \App\Repository\PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findOneBy(['slug' => 'prestations', 'isPublished' => true]);
        if ($page) {
            return $this->render('page/show.html.twig', [
                'page' => $page,
            ]);
        }

        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestationRepository->findBy([], [
                'ordre' => 'ASC',
                'id' => 'ASC'
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