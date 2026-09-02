<?php

namespace App\Command;

use App\Repository\SeanceRepository;
use App\Service\BookingMailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Envoie un e-mail de rappel avec le lien Visio aux clients 48h avant leur séance.',
)]
class SendRemindersCommand extends Command
{
    public function __construct(
        private SeanceRepository $seanceRepository,
        private BookingMailerService $bookingMailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('hours', null, InputOption::VALUE_OPTIONAL, 'Nombre d\'heures avant la séance (48 par défaut)', 48);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $hours = (int) $input->getOption('hours');
        
        $io->title(sprintf('Recherche des séances programmées dans %dh...', $hours));

        $seances = ($hours === 24) 
            ? $this->seanceRepository->findSeancesIn24Hours() 
            : $this->seanceRepository->findSeancesIn48Hours();

        $count = 0;

        if (empty($seances)) {
            $io->info(sprintf('Aucune séance trouvée pour le créneau dans %dh.', $hours));
            return Command::SUCCESS;
        }

        foreach ($seances as $seance) {
            $user = $seance->getUser();

            if (!$user || !$user->getEmail()) {
                continue;
            }

            $success = $this->bookingMailer->sendReminder48hToClient($seance);
            if ($success) {
                $count++;
                $io->text(sprintf('✓ Rappel envoyé à : %s (Séance n°%d - %s)', $user->getEmail(), $seance->getNumero(), $seance->getDateRendezVous()?->format('d/m/Y H:i')));
            } else {
                $io->warning(sprintf('✗ Échec d\'envoi pour : %s', $user->getEmail()));
            }
        }

        $io->success(sprintf('%d e-mail(s) de rappel envoyé(s) avec succès !', $count));

        return Command::SUCCESS;
    }
}