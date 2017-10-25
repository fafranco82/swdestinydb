<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class UpdateDeckProblemsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
        ->setName('app:decks:problems')
        ->setDescription('Update old decks problems after a change of rules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $validator = $this->getContainer()->get('deck_validation_helper');

        $count = 0;
        
        $decks = $em->getRepository('AppBundle:Deck')->findAll();
        foreach($decks as $deck)
        {
            $currentProblem = $deck->getProblem();
            $newProblem = $validator->findProblem($deck);
            if($currentProblem != $newProblem)
            {
                $count++;
                $deck->setProblem($newProblem);
                if($count%20==0) 
                {
                    $em->flush();
                    $output->writeln(date('c') . " $count decks have been updated its problem by now...");
                }
            }
        }
        $em->flush();
        $output->writeln(date('c') . " $count decks in total have been updated its problem.");
    }
}
