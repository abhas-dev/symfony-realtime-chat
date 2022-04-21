<?php

namespace App\Command;

use App\Entity\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'CreateChannel',
    description: 'Creates a Channel',
    aliases: ['app:create-channel']
)]
class CreateChannelCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        string $name = 'create:channel'
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new channel')
            ->setDefinition([
                new InputArgument('name', InputArgument::REQUIRED, 'name')
            ])
            ->setHelp(
                <<<'EOT'
                The <info>create:channel</info> command creates a channel with an <info>name</info> argument
                EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $channel = $this->manager->getRepository(Channel::class)->findOneBy(['name' => $name]);

        if ($channel) {
            throw new \Exception('Channel already exists');
        }

        $channel = (new Channel())->setName($name);

        $this->manager->persist($channel);
        $this->manager->flush();

        return Command::SUCCESS;
    }
}
