<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-expired-temporary-access',
    description: 'Désactive les accès temporaires expirés'
)]
class CleanExpiredTemporaryAccessCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTime();
        $qb = $this->entityManager->createQueryBuilder();

        // Trouver tous les utilisateurs avec accès temporaire actif
        $users = $qb->select('u')
            ->from(User::class, 'u')
            ->where('u.hasTemporaryInvestorAccess = :true')
            ->andWhere('u.temporaryInvestorAccessStart IS NOT NULL')
            ->setParameter('true', true)
            ->getQuery()
            ->getResult();

        $count = 0;

        foreach ($users as $user) {
            $startDate = $user->getTemporaryInvestorAccessStart();

            if (!$startDate) {
                continue;
            }

            $start = $startDate instanceof \DateTime
                ? clone $startDate
                : \DateTime::createFromInterface($startDate);

            $endDate = (clone $start)->add(new \DateInterval('P10D'));

            if ($now > $endDate) {
                $user->setHasTemporaryInvestorAccess(false);
                $count++;

                $io->info(sprintf(
                    'Accès expiré pour %s (début: %s, fin: %s)',
                    $user->getUserIdentifier(),
                    $start->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ));
            }
        }

        if ($count > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d accès temporaire(s) désactivé(s)', $count));
        } else {
            $io->info('Aucun accès temporaire expiré trouvé');
        }

        return Command::SUCCESS;
    }
}
