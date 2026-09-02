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

    // 2. La route pour afficher UNE SEULE prestation en détail (supporte le slug ou l'ID)
    #[Route('/prestation/{slug}', name: 'app_prestation_show')]
    public function show(string $slug, PrestationRepository $prestationRepository): Response
    {
        $prestation = is_numeric($slug) 
            ? $prestationRepository->find((int) $slug) 
            : $prestationRepository->findOneBy(['slug' => $slug]);

        if (!$prestation) {
            throw $this->createNotFoundException('Prestation introuvable.');
        }

        // Si l'utilisateur accède via un ID numérique et qu'un slug existe, redirection canonique 301
        if (is_numeric($slug) && $prestation->getSlug()) {
            return $this->redirectToRoute('app_prestation_show', ['slug' => $prestation->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->render('prestation/show.html.twig', [
            'prestation' => $prestation,
        ]);
    }
    
}