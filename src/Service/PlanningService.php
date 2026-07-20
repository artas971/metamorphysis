<?php

namespace App\Service;

use App\Repository\HoraireHebdomadaireRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\SeanceRepository; // MODIFICATION ICI : On utilise SeanceRepository
use DateTimeInterface;

class PlanningService
{
    public function __construct(
        private HoraireHebdomadaireRepository $horaireRepo,
        private IndisponibiliteRepository $indispoRepo,
        private SeanceRepository $seanceRepo // MODIFICATION ICI
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

        // MODIFICATION ICI : On exclut les séances annulées pour libérer la place
        $seancesPrises = $this->seanceRepo->createQueryBuilder('s')
            ->where('s.dateRendezVous >= :debut')
            ->andWhere('s.dateRendezVous <= :fin')
            ->andWhere('s.statut != :annule')
            ->setParameter('debut', $debutJournee)
            ->setParameter('fin', $finJournee)
            ->setParameter('annule', 'Annulé')
            ->getQuery()
            ->getResult();

        $intervallesReserves = [];
        foreach ($seancesPrises as $seance) {
            $debutResa = clone $seance->getDateRendezVous();
            $finResa = clone $debutResa;
            
            // MODIFICATION ICI : On récupère la durée directement sur l'entité Seance
            $duree = $seance->getDuree() ?? 60;
            $finResa->modify("+".($duree + 15)." minutes");

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
        $pas = 15; // Proposition de rendez-vous toutes les 15 minutes
        $tempsPause = 15; // Temps de battement obligatoire entre deux séances

        $actuel = \DateTime::createFromInterface($date);
        $actuel->setTime((int)$ouverture->format('H'), (int)$ouverture->format('i'));

        $limite = \DateTime::createFromInterface($date);
        $limite->setTime((int)$fermeture->format('H'), (int)$fermeture->format('i'));

        while ($actuel < $limite) {
            // Heure de fin réelle de la séance
            $finSoin = clone $actuel;
            $finSoin->modify("+{$dureePrestation} minutes");

            // La séance seule (sans la pause) doit se terminer avant la fermeture de l'institut
            if ($finSoin > $limite) {
                break;
            }

            // Pour vérifier les disponibilités, on inclut le temps de pause obligatoire après le soin
            $finAvecPause = clone $finSoin;
            $finAvecPause->modify("+{$tempsPause} minutes");

            $chevauchement = false;
            foreach ($intervallesReserves as $intervalle) {
                // On croise le créneau potentiel (+ sa pause) avec les réservations existantes (+ leur pause)
                if ($actuel < $intervalle['fin'] && $finAvecPause > $intervalle['debut']) {
                    $chevauchement = true;
                    break;
                }
            }

            if (!$chevauchement) {
                // BYPASS DU BUG JIT
                array_push($blocs, $actuel->format('H:i'));
            }

            // On avance de 15 minutes pour vérifier le créneau suivant
            $actuel->modify("+{$pas} minutes");
        }

        return $blocs;
    }
}