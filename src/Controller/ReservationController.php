<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationController extends AbstractController
{
    #[Route('/reserver/{id}', name: 'app_reservation_new')]
    #[IsGranted('ROLE_USER')]
    public function reserver(Prestation $prestation, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $reservation = new Reservation();
        
        $reservation->setUser($this->getUser());
        $reservation->setPrestation($prestation);
        $reservation->setStatut('En attente');

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            // --- NOUVEAU : Envoi du mail à l'administratrice ---
            $email = (new TemplatedEmail())
                ->from('noreply@metamorphysis.com') // L'adresse d'expédition du site
                ->to('admin@metamorphysis.com') // L'adresse de ta cliente
                ->subject('Nouvelle demande de réservation !')
                ->htmlTemplate('emails/nouvelle_reservation.html.twig')
                ->context([
                    'reservation' => $reservation,
                    'client' => $this->getUser(),
                    'prestation' => $prestation
                ]);

            $mailer->send($email);
            // ----------------------------------------------------

            $this->addFlash('success', 'Votre demande de réservation a bien été envoyée !');

            return $this->redirectToRoute('app_prestation_index');
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form->createView(),
            'prestation' => $prestation,
        ]);
    }
    #[Route('/reservation/{id}', name: 'app_reservation_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Reservation $reservation): Response
    {
        // SÉCURITÉ : On vérifie que la réservation appartient bien à l'utilisateur connecté
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce rendez-vous.');
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}