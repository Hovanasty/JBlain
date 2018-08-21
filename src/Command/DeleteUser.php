<?php
namespace App\Command;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUser extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:delete-user')
            ->setDescription('supprime les comptes non activés depuis 5 jours')
            ->setHelp('supprime les comptes non activés depuis 5 jours');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class);
        $deleteUserAction = $user->deletedInactiveAccount();
        $output->writeln([
            '============',
            $deleteUserAction.' user(s) deleted',
            '============',
        ]);
    }
}
