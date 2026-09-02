<?php

namespace App\Service;

use App\Repository\HoraireHebdomadaireRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\SeanceRepository;
use DateTimeInterface;
use Psr\Cache\CacheItemPoolInterface; // <-- Ajout de l'interface pour lire les verrous

class PlanningService
{
    public function __construct(
        private HoraireHebdomadaireRepository $horaireRepo,
        private IndisponibiliteRepository $indispoRepo,
        private SeanceRepository $seanceRepo,
        private CacheItemPoolInterface $cache // <-- Injection du service de cache
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
            
            $duree = $seance->getDuree() ?? 60;
            // PASSAGE À 20 MINUTES de battement après un rendez-vous existant en base
            $finResa->modify("+".($duree + 20)." minutes");

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
        
        // MODIFICATIONS : Affichage des créneaux toutes les 20 min et 20 min de battement obligatoire
        $pas = 20; 
        $tempsPause = 20; 

        $actuel = \DateTime::createFromInterface($date);
        $actuel->setTime((int)$ouverture->format('H'), (int)$ouverture->format('i'));

        $limite = \DateTime::createFromInterface($date);
        $limite->setTime((int)$fermeture->format('H'), (int)$fermeture->format('i'));

        $maintenant = new \DateTime();
        $delaiMin6h = (clone $maintenant)->modify('+6 hours');

        while ($actuel < $limite) {
            // RÈGLE MÉTIER : Impossible de réserver moins de 6 heures à l'avance
            if ($actuel < $delaiMin6h) {
                $actuel->modify("+{$pas} minutes");
                continue;
            }

            // Heure de fin réelle de la séance
            $finSoin = clone $actuel;
            $finSoin->modify("+{$dureePrestation} minutes");

            // La séance seule (sans la pause) doit se terminer avant la fermeture
            if ($finSoin > $limite) {
                break;
            }

            // On inclut le temps de pause obligatoire après le soin
            $finAvecPause = clone $finSoin;
            $finAvecPause->modify("+{$tempsPause} minutes");

            $chevauchement = false;
            
            // 1. Vérification avec les réservations déjà enregistrées en base
            foreach ($intervallesReserves as $intervalle) {
                if ($actuel < $intervalle['fin'] && $finAvecPause > $intervalle['debut']) {
                    $chevauchement = true;
                    break;
                }
            }

            // 2. VERROU TEMPORAIRE STRIPE : Vérification du cache
            if (!$chevauchement) {
                // On recrée exactement la même clé que celle du ReservationController
                $lockKey = 'lock_' . $actuel->format('Y-m-d_H-i');
                
                // Si la clé existe dans le cache, c'est que quelqu'un est en train de payer ce créneau
                if ($this->cache->hasItem($lockKey)) {
                    $chevauchement = true;
                }
            }

            // Si tout est libre, on valide l'horaire
            if (!$chevauchement) {
                array_push($blocs, $actuel->format('H:i'));
            }

            // On avance au créneau suivant (dans 20 minutes)
            $actuel->modify("+{$pas} minutes");
        }

        return $blocs;
    }
}