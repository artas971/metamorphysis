<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                        // On récupère la valeur du faux champ
            $honeypot = $form->get('fax_number')->getData();

            // Si le champ n'est pas vide, c'est un robot !
            if (!empty($honeypot)) {
                // On fait croire au bot que ça a marché pour qu'il ne cherche pas d'autres failles
                return $this->redirectToRoute('app_home'); 
            }

// Sinon, on continue le traitement normal (envoi du mail, sauvegarde en BDD...)
            $data = $form->getData();

            // Création de l'e-mail
            $email = (new Email())
                ->from($data['email'])
                ->to('contact@metamorphysis.com') // L'adresse de l'administratrice
                ->subject('Nouveau message de : ' . $data['nom'] . ' - ' . $data['sujet'])
                ->text($data['message']);

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}