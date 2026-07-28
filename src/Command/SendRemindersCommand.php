<?php

namespace App\Command;

use App\Repository\SeanceRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Envoie un e-mail de rappel aux clients 24h avant leur séance.',
)]
class SendRemindersCommand extends Command
{
    public function __construct(
        private SeanceRepository $seanceRepository,
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Recherche des séances dans 24h...');

        $seances = $this->seanceRepository->findSeancesIn24Hours();
        $count = 0;

        if (empty($seances)) {
            $io->info('Aucune séance trouvée pour les prochaines 24h.');
            return Command::SUCCESS;
        }

        foreach ($seances as $seance) {
            $user = $seance->getUser();

            if (!$user || !$user->getEmail()) {
                continue; // On ignore si l'utilisateur n'a pas d'e-mail
            }

            $email = (new TemplatedEmail())
                ->from('noreply@metamorphysis.com')
                ->to($user->getEmail())
                ->subject('Rappel : Votre séance Metamorphysis est demain !')
                ->htmlTemplate('emails/client_rappel_seance.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $user,
                    'prestation' => $seance->getPrestation()
                ]);

            $this->mailer->send($email);
            $count++;
            
            $io->text('Rappel envoyé à : ' . $user->getEmail() . ' (Séance n°' . $seance->getNumero() . ')');
        }

        $io->success(sprintf('%d e-mail(s) de rappel envoyé(s) avec succès !', $count));

        return Command::SUCCESS;
    }
}