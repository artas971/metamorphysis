<?php

namespace App\Service;

use App\Repository\HoraireHebdomadaireRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\ReservationRepository;
use DateTimeInterface;

class PlanningService
{
    public function __construct(
        private HoraireHebdomadaireRepository $horaireRepo,
        private IndisponibiliteRepository $indispoRepo,
        private ReservationRepository $reservationRepo
    ) {}

    public function getCreneauxDisponibles(DateTimeInterface $dateRecherchee, int $dureePrestation): array
    {
        $jourSemaine = (int) $dateRecherchee->format('N');
        $horaire = $this->horaireRepo->findOneBy(['jour' => $jourSemaine, 'estOuvert' => true]);

        if (!$horaire) {
            return [];
        }

        $debutJournee = \DateTime::createFromInterface($dateRecherchee);
        $debutJournee->setTime(0, 0, 0);
        
        $finJournee = \DateTime::createFromInterface($dateRecherchee);
        $finJournee->setTime(23, 59, 59);

        $indisponibilites = $this->indispoRepo->createQueryBuilder('i')
            ->where('i.debut <= :fin')
            ->andWhere('i.fin >= :debut')
            ->setParameter('debut', $debutJournee)
            ->setParameter('fin', $finJournee)
            ->getQuery()
            ->getResult();

        if (count($indisponibilites) > 0) {
            return [];
        }

        $reservations = $this->reservationRepo->createQueryBuilder('r')
            ->where('r.dateRendezVous >= :debut')
            ->andWhere('r.dateRendezVous <= :fin')
            ->setParameter('debut', $debutJournee)
            ->setParameter('fin', $finJournee)
            ->getQuery()
            ->getResult();

        $intervallesReserves = [];
        foreach ($reservations as $resa) {
            $debutResa = clone $resa->getDateRendezVous();
            $finResa = clone $debutResa;
            
            $duree = $resa->getPrestation()->getDuree();
            $finResa->modify("+{$duree} minutes");

            // BYPASS DU BUG JIT
            array_push($intervallesReserves, [
                'debut' => $debutResa,
                'fin' => $finResa
            ]);
        }

        $creneaux = [];

        if ($horaire->getOuvertureMatin() && $horaire->getFermetureMatin()) {
            $creneaux = array_merge($creneaux, $this->genererBlocsFiltres($dateRecherchee, $horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $dureePrestation, $intervallesReserves));
        }

        if ($horaire->getOuvertureApresMidi() && $horaire->getFermetureApresMidi()) {
            $creneaux = array_merge($creneaux, $this->genererBlocsFiltres($dateRecherchee, $horaire->getOuvertureApresMidi(), $horaire->getFermetureApresMidi(), $dureePrestation, $intervallesReserves));
        }

        return $creneaux;
    }

    private function genererBlocsFiltres(DateTimeInterface $date, \DateTimeInterface $ouverture, \DateTimeInterface $fermeture, int $dureePrestation, array $intervallesReserves): array
    {
        $blocs = [];
        $pas = 30;

        $actuel = \DateTime::createFromInterface($date);
        $actuel->setTime((int)$ouverture->format('H'), (int)$ouverture->format('i'));

        $limite = \DateTime::createFromInterface($date);
        $limite->setTime((int)$fermeture->format('H'), (int)$fermeture->format('i'));

        while ($actuel < $limite) {
            $finCreneauPossible = clone $actuel;
            $finCreneauPossible->modify("+{$dureePrestation} minutes");

            if ($finCreneauPossible > $limite) {
                break;
            }

            $chevauchement = false;
            foreach ($intervallesReserves as $intervalle) {
                if ($actuel < $intervalle['fin'] && $finCreneauPossible > $intervalle['debut']) {
                    $chevauchement = true;
                    break;
                }
            }

            if (!$chevauchement) {
                // BYPASS DU BUG JIT
                array_push($blocs, $actuel->format('H:i'));
            }

            $actuel->modify("+{$pas} minutes");
        }

        return $blocs;
    }
}