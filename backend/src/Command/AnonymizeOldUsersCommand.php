<?php

namespace App\Command;

use App\Entity\ConsentLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:anonymize-old-users',
    description: 'Anonymise les comptes inactifs depuis plus de 2 ans.'
)]
class AnonymizeOldUsersCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limitDate = new \DateTime('-2 years');

        $users = $this->entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            if ($user->getCreatedAt() !== null && $user->getCreatedAt() < $limitDate && !$user->isAnonymized()) {
                $originalEmail = $user->getEmail() ?? '';

                $user
                    ->setFirstName('Utilisateur supprimé')
                    ->setLastName('Utilisateur supprimé')
                    ->setEmail(hash('sha256', $originalEmail))
                    ->setPhone(null)
                    ->setIsAnonymized(true);

                $log = new ConsentLog();
                $log
                    ->setUser($user)
                    ->setAction('data_deleted')
                    ->setTimestamp(new \DateTime())
                    ->setIpAddress(hash('sha256', 'cron'))
                    ->setDetails('Anonymisation automatique des comptes inactifs depuis plus de 2 ans');

                $this->entityManager->persist($log);
            }
        }

        $this->entityManager->flush();

        $output->writeln('Anonymisation terminée.');

        return Command::SUCCESS;
    }
}