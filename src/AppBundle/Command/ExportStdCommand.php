<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\BufferedOutput;

class ExportStdCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('app:export:std')
		->setDescription('Create JSON Data Files')
		->addArgument(
				'path',
				InputArgument::REQUIRED,
				'Path to the repository'
		)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$fs = new Filesystem();
		
		$path = $input->getArgument('path');
			
		if(substr($path, -1) === '/') {
			$path = substr($path, 0, strlen($path) - 1); 
		}
		
		$output->writeln("Exporting data in <info>$path</info>");
		
		$things = ['affiliation', 'faction', 'rarity', 'set', 'type', 'subtype', 'sideType', 'starterPack'];
		
		foreach($things as $thing) 
		{
			$plural = $thing === 'rarity' ? 'rarities' : "${thing}s";
			$filepath = "${path}/${plural}.json";
			$output->writeln("Exporting to <info>$filepath</info>");
			
			$command = $this->getApplication()->find('app:dump:std:base');
			$arguments = [ 'entityName' => $thing ];
			$subInput = new ArrayInput($arguments);
			$subOutput = new BufferedOutput();
			$returnCode = $command->run($subInput, $subOutput);
			
			if($returnCode == 0) {
				$fs->dumpFile($filepath, $subOutput->fetch());
			} else {
				throw new \Exception("An error occured (code $returnCode)");
			}
		}
		
		$sets = $this->getContainer()->get('doctrine')->getManager()->getRepository('AppBundle:Set')->findAll();
		
		foreach($sets as $set) {
			$set_code = $set->getCode();
			$filepath = "${path}/set/${set_code}.json";
			$output->writeln("Exporting to <info>$filepath</info>");
	
			$command = $this->getApplication()->find('app:dump:std:cards');
			$arguments = [ 'set_code' => $set_code ];
			$subInput = new ArrayInput($arguments);
			$subOutput = new BufferedOutput();
			$returnCode = $command->run($subInput, $subOutput);
	
			if($returnCode == 0) {
				$fs->dumpFile($filepath, $subOutput->fetch());
			} else {
				throw new \Exception("An error occured (code $returnCode)");
			}
		}
	}
}