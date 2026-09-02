<?php

namespace App\EventSubscriber;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Service\BookingMailerService;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReservationConfirmedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BookingMailerService $bookingMailer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityUpdatedEvent::class => ['onReservationConfirmed'],
        ];
    }

    public function onReservationConfirmed(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        // On vérifie que c'est une Séance qui passe en "Confirmé"
        if ($entity instanceof Seance && $entity->getStatut() === 'Confirmé') {
            $this->bookingMailer->sendBookingConfirmedToClient($entity);
        }
    }
}