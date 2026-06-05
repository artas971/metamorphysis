<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request): Response
    {
        // 1. On crée l'instance du formulaire basé sur le ContactType
        $form = $this->createForm(ContactType::class);

        // 2. On écoute la requête (pour savoir si le formulaire a été soumis plus tard)
        $form->handleRequest($request);

        // 3. On envoie le formulaire à la vue (Twig)
        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}