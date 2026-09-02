<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function renderDynamicNav(PageRepository $pageRepository): Response
    {
        // Si l'utilisateur est connecté en tant qu'Admin (EasyAdmin / Aperçu), 
        // on affiche toutes les pages avec afficherMenu = true même si elles sont en cours de rédaction (isPublished = false)
        if ($this->isGranted('ROLE_ADMIN')) {
            $pages = $pageRepository->findBy([
                'afficherMenu' => true
            ], ['ordreMenu' => 'ASC', 'id' => 'ASC']);
        } else {
            // Pour les visiteurs publics, la page doit être publiée ET le menu coché
            $pages = $pageRepository->findBy([
                'afficherMenu' => true,
                'isPublished' => true
            ], ['ordreMenu' => 'ASC', 'id' => 'ASC']);
        }

        return $this->render('partials/_nav_links.html.twig', [
            'pages' => $pages,
        ]);
    }

    public function renderDynamicFooter(PageRepository $pageRepository): Response
    {
        $criteria = ['afficherFooter' => true];
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['isPublished'] = true;
        }

        $pages = $pageRepository->findBy($criteria, ['ordreMenu' => 'ASC', 'id' => 'ASC']);

        return $this->render('partials/_footer_links.html.twig', [
            'pages' => $pages,
        ]);
    }
}