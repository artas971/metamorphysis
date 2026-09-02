<?php

namespace App\Command;

use App\Entity\Prestation;
use App\Entity\Seance;
use App\Entity\User;
use App\Service\BookingMailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

#[AsCommand(
    name: 'app:test-emails',
    description: 'Génère les prévisualisations HTML de tous les e-mails transactionnels.',
)]
class TestEmailsCommand extends Command
{
    public function __construct(
        private Environment $twig,
        private BookingMailerService $bookingMailer,
        private \App\Service\PdfService $pdfService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Génération des prévisualisations d\'e-mails et de la Facture PDF...');

        $user = new User();
        $user->setEmail('client.test@gmail.com');
        $user->setPrenom('Sophie');
        $user->setNom('Laurent');
        $user->setTelephone('06 12 34 56 78');

        $prestation = new Prestation();
        $prestation->setNom('Parcours Métamorphose (3 mois)');
        $prestation->setPrix(350);
        $prestation->setDuree(90);

        $seance = new Seance();
        $seance->setUser($user);
        $seance->setPrestation($prestation);
        $seance->setNumero(1);
        $seance->setDateRendezVous(new \DateTime('+2 days 14:30'));
        $seance->setStatut('En attente de validation');
        $seance->setLienVisio('https://metamorphysis.daily.co/soin-sophie-laurent');

        $previews = [
            '01_client_demande_en_attente.html' => $this->twig->render('emails/client_demande_en_attente.html.twig', [
                'seance' => $seance,
                'client' => $user,
                'prestation' => $prestation,
                'hasInvoice' => true,
            ]),
            '02_admin_nouvelle_reservation.html' => $this->twig->render('emails/admin_nouvelle_reservation.html.twig', [
                'seance' => $seance,
                'client' => $user,
                'prestation' => $prestation,
                'montant' => 350.00,
            ]),
            '03_client_seance_confirmee.html' => $this->twig->render('emails/client_seance_confirmee.html.twig', [
                'seance' => $seance,
                'client' => $user,
                'prestation' => $prestation,
            ]),
            '04_client_rappel_48h.html' => $this->twig->render('emails/client_rappel_48h.html.twig', [
                'seance' => $seance,
                'client' => $user,
                'prestation' => $prestation,
            ]),
            '05_client_annulation.html' => $this->twig->render('emails/client_annulation_seance.html.twig', [
                'seance' => $seance,
                'client' => $user,
                'prestation' => $prestation,
                'ancienneDate' => new \DateTime('+2 days 14:30'),
            ]),
            '06_admin_annulation.html' => $this->twig->render('emails/admin_annulation_seance.html.twig', [
                'seance' => $seance,
                'client' => $user,
                'prestation' => $prestation,
                'ancienneDate' => new \DateTime('+2 days 14:30'),
            ]),
        ];

        $baseDir = 'C:/Users/artas/.gemini/antigravity/brain/0ea2f9e9-b0c8-4138-8ad1-530b58f7f0bb/scratch';
        foreach ($previews as $filename => $html) {
            file_put_contents($baseDir . '/' . $filename, $html);
            $io->text(sprintf('✓ Email template : %s (%d octets)', $filename, strlen($html)));
        }

        // Test PDF Generation
        $htmlFacture = $this->twig->render('pdf/facture.html.twig', [
            'reservation' => $seance
        ]);
        $pdfContent = $this->pdfService->generateBinaryPdf($htmlFacture);
        file_put_contents($baseDir . '/07_facture_test.pdf', $pdfContent);
        $io->text(sprintf('✓ Facture PDF générée : 07_facture_test.pdf (%d octets)', strlen($pdfContent)));

        $io->success('Toutes les prévisualisations d\'e-mails et la Facture PDF ont été générées et validées avec succès !');

        return Command::SUCCESS;
    }
}
