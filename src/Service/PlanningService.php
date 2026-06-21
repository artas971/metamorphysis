<?php

namespace App\Service;

use App\Repository\HoraireHebdomadaireRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\ReservationRepository;
use DateTimeInterface;
use DateInterval;

class PlanningService
{
    public function __construct(
        private HoraireHebdomadaireRepository $horaireRepo,
        private IndisponibiliteRepository $indispoRepo,
        private ReservationRepository $reservationRepo
    ) {}

    /**
     * Calcule tous les créneaux disponibles pour une date donnée.
     */
    public function getCreneauxDisponibles(DateTimeInterface $dateRecherchee, int $dureeMinutes = 30): array
    {
        $creneaux = [];

        // 1. Quel jour de la semaine sommes-nous ? (1 = Lundi, 7 = Dimanche)
        $jourSemaine = (int) $dateRecherchee->format('N');
        $horaire = $this->horaireRepo->findOneBy(['jour' => $jourSemaine, 'estOuvert' => true]);

        // Si tu n'as pas activé ce jour dans "Ma Semaine Type", on renvoie un tableau vide.
        if (!$horaire) {
            return [];
        }

        // 2. Y a-t-il une fermeture exceptionnelle (Indisponibilité) ce jour-là ?
        $debutJournee = clone $dateRecherchee;
        $debutJournee->setTime(0, 0, 0);
        $finJournee = clone $dateRecherchee;
        $finJournee->setTime(23, 59, 59);

        // On interroge la base de données pour voir si une absence croise cette journée
        $indisponibilites = $this->indispoRepo->createQueryBuilder('i')
            ->where('i.debut <= :fin')
            ->andWhere('i.fin >= :debut')
            ->setParameter('debut', $debutJournee)
            ->setParameter('fin', $finJournee)
            ->getQuery()
            ->getResult();

        if (count($indisponibilites) > 0) {
            // Si une indisponibilité touche ce jour, le cabinet est fermé.
            return [];
        }

        // 3. Découpage de la journée en blocs (Matin et Après-midi)
        if ($horaire->getOuvertureMatin() && $horaire->getFermetureMatin()) {
            $creneaux = array_merge($creneaux, $this->genererBlocs($dateRecherchee, $horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $dureeMinutes));
        }

        if ($horaire->getOuvertureApresMidi() && $horaire->getFermetureApresMidi()) {
            $creneaux = array_merge($creneaux, $this->genererBlocs($dateRecherchee, $horaire->getOuvertureApresMidi(), $horaire->getFermetureApresMidi(), $dureeMinutes));
        }

        // 4. Étape Finale : Filtrer les créneaux déjà réservés par d'autres clients.
        // (Nous allons l'ajouter juste après).

        return $creneaux;
    }

    /**
     * Fonction utilitaire : Découpe un intervalle de temps en boutons de X minutes.
     */
    private function genererBlocs(DateTimeInterface $date, \DateTimeInterface $ouverture, \DateTimeInterface $fermeture, int $dureeMinutes): array
    {
        $blocs = [];
        $actuel = clone $date;
        $actuel->setTime((int)$ouverture->format('H'), (int)$ouverture->format('i'));

        $limite = clone $date;
        $limite->setTime((int)$fermeture->format('H'), (int)$fermeture->format('i'));

        while ($actuel < $limite) {
            $finCreneau = clone $actuel;
            $finCreneau->add(new DateInterval('PT' . $dureeMinutes . 'M'));

            // Si le créneau déborde sur la fermeture (ex: un rdv d'1h qui commence 15min avant la fermeture), on l'annule.
            if ($finCreneau > $limite) {
                break;
            }

            $blocs[] = $actuel->format('H:i'); // On stocke l'heure sous format texte "09:00"
            $actuel = $finCreneau;
        }

        return $blocs;
    }
}