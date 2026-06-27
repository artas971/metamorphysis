<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PageController extends AbstractController
{ 
    // LA ROUTE PUBLIQUE
    #[Route('/{slug}', name: 'app_page_show', priority: -1)]
    public function show(string $slug, PageRepository $pageRepository): Response
    {
        // Le public ne peut voir QUE les pages en ligne
        $page = $pageRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$page) {
            // Si la page est en brouillon, elle renvoie une 404 pour le public
            throw $this->createNotFoundException('Cette page n\'est pas disponible ou est en cours de rédaction.');
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }

    // LA ROUTE D'APERÇU SÉCURISÉE
    #[Route('/admin/preview/{slug}', name: 'app_page_preview')]
    #[IsGranted('ROLE_ADMIN')] // Seul un admin peut entrer ici
    public function preview(string $slug, PageRepository $pageRepository): Response
    {
        // On cherche la page par son slug, PEU IMPORTE si elle est publiée ou non
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if (!$page) {
            throw $this->createNotFoundException('Cette page n\'existe pas.');
        }

        // On utilise le même template de rendu, mais on lui passe la variable 'isDraftPreview'
        return $this->render('page/show.html.twig', [
            'page' => $page,
            'isDraftPreview' => !$page->isPublished(), // Pour afficher le bandeau
        ]);
    }
}