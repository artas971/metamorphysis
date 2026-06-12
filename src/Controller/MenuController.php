<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function renderDynamicNav(PageRepository $pageRepository): Response
    {
        // On récupère uniquement les pages où afficherMenu est à "true" (coché)
        $pages = $pageRepository->findBy(['afficherMenu' => true]);
        return $this->render('partials/_nav_links.html.twig', [
            'pages' => $pages,
        ]);
    }
}