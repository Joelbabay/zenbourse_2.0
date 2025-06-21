<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate-slugs')]
class GenerateSlugsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Génère les slugs pour les entités qui n\'en ont pas')
            ->setHelp('Cette commande permet de générer des slugs pour les entités qui n\'en ont pas encore.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Logique pour générer les slugs
        $output->writeln('Génération des slugs en cours...');

        // Ici, vous ajouteriez la logique pour parcourir vos entités et générer les slugs

        $output->writeln('Slugs générés avec succès !');
        return Command::SUCCESS;
    }
}
