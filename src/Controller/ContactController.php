<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            
            // On récupère toutes les données du formulaire
            $donnees = $form->getData();

            // On prépare l'e-mail
            $email = (new Email())
                ->from($donnees['email']) // L'e-mail saisi par le visiteur
                ->to('contact@metamorphysis.fr') // Ton adresse de réception
                ->subject('Nouvelle demande : ' . $donnees['sujet'])
                ->text(
                    "Nom : " . $donnees['nom'] . "\n" .
                    "Email : " . $donnees['email'] . "\n\n" .
                    "Message :\n" . $donnees['message']
                );

            // On envoie l'e-mail
            $mailer->send($email);

            // On ajoute un message de succès pour l'utilisateur
            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons très vite !');

            // On redirige vers la page de contact pour vider le formulaire
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}