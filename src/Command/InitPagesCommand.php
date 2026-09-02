<?php

namespace App\Command;

use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-pages',
    description: 'Initialise ou met à jour les 4 pages natives par défaut (Mentions Légales, À Propos, L\'Expérience, À vous).',
)]
class InitPagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🚀 Initialisation des 4 pages natives Métamorphysis...');

        $fixtures = new AppFixtures();
        $fixtures->load($this->em);

        $io->success('Les 4 pages natives ont été synchronisées avec succès en base de données !');
        return Command::SUCCESS;
    }
}
