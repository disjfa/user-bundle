<?php

namespace Disjfa\UserBundle\Command;

use Disjfa\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:promote',
    description: 'Add a short description for your command',
)]
class PromoteUserCommand extends Command
{
    public function __construct(public readonly UserRepository $userRepository, public readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $emailQuestion = new Question('Email?');
        $email = $io->askQuestion($emailQuestion);

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            $io->error('User not found');

            return Command::FAILURE;
        }

        $rolesQuestion = new ChoiceQuestion('Assign role?', ['ROLE_USER', 'ROLE_ADMIN'], 0);

        $roles = $user->getRoles();
        $roles[] = $io->askQuestion($rolesQuestion);
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User promoted!');

        return Command::SUCCESS;
    }
}
