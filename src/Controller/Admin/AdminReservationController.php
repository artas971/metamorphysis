<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminReservationController extends AbstractController
{
    #[Route('/reservations', name: 'app_admin_reservations')]
    public function index(SeanceRepository $seanceRepository): Response
    {
        // Récupération de toutes les séances planifiées, triées par date de rendez-vous
        $seances = $seanceRepository->createQueryBuilder('s')
            ->where('s.dateRendezVous IS NOT NULL')
            ->orderBy('s.dateRendezVous', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/reservations/index.html.twig', [
            'seances' => $seances,
        ]);
    }

    #[Route('/reservation/{id}/valider', name: 'app_admin_reservation_valider')]
    public function valider(
        Seance $seance, 
        EntityManagerInterface $em,
        \App\Service\BookingMailerService $bookingMailer,
        \App\Service\DailyCoService $dailyCoService
    ): Response {
        $seance->setStatut('Confirmé');

        if (!$seance->getLienVisio() && $seance->getDateRendezVous()) {
            $lien = $dailyCoService->createRoom($seance->getDateRendezVous());
            if ($lien) {
                $seance->setLienVisio($lien);
            }
        }

        $em->flush();

        $bookingMailer->sendBookingConfirmedToClient($seance);

        $this->addFlash('success', 'La séance n°' . $seance->getNumero() . ' a été confirmée et un e-mail avec le lien de visioconférence et la facture a été envoyé au client.');
        return $this->redirectToRoute('app_admin_reservations');
    }

    #[Route('/reservation/{id}/annuler', name: 'app_admin_reservation_annuler')]
    public function annuler(
        Seance $seance, 
        EntityManagerInterface $em,
        \App\Service\BookingMailerService $bookingMailer
    ): Response {
        $ancienneDate = $seance->getDateRendezVous();
        $seance->setStatut('Annulé');
        $em->flush();

        $bookingMailer->sendCancellationToClient($seance, $ancienneDate);

        $this->addFlash('info', 'La séance n°' . $seance->getNumero() . ' de ' . $seance->getUser()->getPrenom() . ' a été annulée. Le client peut désormais réserver à nouveau ce type de consultation.');
        return $this->redirectToRoute('app_admin_reservations');
    }
}