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
    name: 'DeleteChannel',
    description: 'Add a short description for your command',
    aliases: ['app:delete-channel']
)]
class DeleteChannelCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        string $name = 'delete:channel'
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete a channel')
            ->setDefinition([
                new InputArgument('name', InputArgument::REQUIRED, 'name')
            ])
            ->setHelp(
                <<<'EOT'
                The <info>delete:channel</info> command delete a channel regarding an <info>name</info> argument
                EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $channel = $this->manager->getRepository(Channel::class)->findOneBy(['name' => $name]);

        if (!$channel) {
            throw new \Exception('Channel does not exists');
        }

        $this->manager->remove($channel);
        $this->manager->flush();

        return Command::SUCCESS;
    }
}
