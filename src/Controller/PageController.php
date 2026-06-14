<?php

namespace App\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{ 
    // 2. LES AUTRES PAGES (URL propre, sans le mot /page/)
    // L'astuce magique est "priority: -1" : Symfony testera cette route en tout dernier !
    #[Route('/{slug}', name: 'app_page_show', priority: -1)]
    public function show(string $slug, EntityManagerInterface $entityManager): Response
    {
        $page = $entityManager->getRepository(Page::class)->findOneBy(['slug' => $slug]);

        if (!$page || !$page->isPublished()) {
            throw $this->createNotFoundException('Cette page est en cours de rédaction ou n\'existe pas.');
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }
}