<?php

namespace App\Command;

use App\Entity\Streamer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-streamer',
    description: 'Add a short description for your command',
)]
class CreateStreamerCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Streamer name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('name');

        if ($this->entityManager->getRepository(Streamer::class)->findOneBy(['name' => $arg1])) {
            $io->error('Streamer already exists.');
            return Command::FAILURE;
        }

        $streamer = new Streamer();
        $streamer->setName($arg1);
        $streamer->setKey(bin2hex(random_bytes(10)));
        $streamer->setViewKey(bin2hex(random_bytes(10)));

        $this->entityManager->persist($streamer);
        $this->entityManager->flush();

        $io->success('Streamer created.');
        $io->success('Key: ' . $streamer->getKey());
        $io->success('ViewKey: ' . $streamer->getViewKey());

        return Command::SUCCESS;
    }
}
