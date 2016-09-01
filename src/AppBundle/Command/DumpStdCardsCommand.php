<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DumpStdCardsCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('app:dump:std:cards')
		->setDescription('Dump JSON Data of Cards from a Set')
		->addArgument(
				'set_code',
				InputArgument::REQUIRED,
				"Set Code"
		)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$set_code = $input->getArgument('set_code');
		
		$set = $this->getContainer()->get('doctrine')->getManager()->getRepository('AppBundle:Set')->findOneBy(['code' => $set_code]);
		
		if(!$set) {
			throw new \Exception("Set [$set_code] cannot be found.");
		}
		
		/* @var $repository \AppBundle\Repository\CardRepository */
		$repository = $this->getContainer()->get('doctrine')->getManager()->getRepository('AppBundle:Card');
		
		$qb = $repository->createQueryBuilder('c')
			->select('c, s, st')
			->leftJoin('c.sides', 's')
			->leftJoin('s.type', 'st')
			->where('c.set = :set')
			->setParameter('set', $set)
			->orderBy('c.code');
		
		$cards = $qb->getQuery()->getResult();
		
		$arr = [];
		
		foreach($cards as $card) {
			$arr[] = $card->serialize();
		}
		
		$output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ));
		$output->writeln("");
	}
}