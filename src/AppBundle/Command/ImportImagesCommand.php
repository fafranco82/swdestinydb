<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ImportImagesCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('app:import:images')
		->setDescription('Download missing card images from FFG websites')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$assets_helper = $this->getContainer()->get('templating.helper.assets');

		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->getContainer()->get('doctrine')->getManager();

		$rootDir = $this->getContainer()->get('kernel')->getRootDir();

		$cards = $em->getRepository('AppBundle:Card')->findBy([], ['code' => 'ASC']);
		/* @var \AppBundle\Entity\Card $card */
		foreach($cards as $card) {
			$card_code = $card->getCode();
			$set_code = sprintf("%02d", $card->getSet()->getPosition());
			
			/*
			if(!$card->getSet()->getDateRelease()) {
				$output->writeln("Skip $card_code because it's not released");
				continue;
			}
			*/
			
			if(!$card->getSet()->getCgdbIdStart() || !$card->getSet()->getCgdbIdEnd()) {
				$output->writeln("Skip $card_code because its cgdb_id is not defined");
				continue;
			}
				
			$imageurl = $assets_helper->getUrl('bundles/cards/en/'.$set_code.'/'.$card_code.'.jpg');
			$imagepath= $rootDir . '/../web' . preg_replace('/\?.*/', '', $imageurl);
			
			if(file_exists($imagepath)) {
				$output->writeln("Skip $card_code because it's already there");
			} else {
				$dirname = dirname($imagepath);
				$outputfile = $dirname . DIRECTORY_SEPARATOR . $card_code . ".jpg";

				$written = FALSE;
				$cgdbid = $card->getSet()->getCgdbIdEnd();

				while($cgdbid >= $card->getSet()->getCgdbIdStart())
				{
					$cgdbfile = sprintf('SWD%02d_%d.jpg', $cgdbid, $card->getPosition());
					$cgdburl = "http://lcg-cdn.fantasyflightgames.com/swd/" . $cgdbfile;

					$image = @file_get_contents($cgdburl);
					if($image !== FALSE) {
						file_put_contents($outputfile, $image);
						$output->writeln("New file at $outputfile");
						$written = TRUE;
					}

					$cgdbid--;
				}

				if(!$written) {
					$output->writeln("Failed at downloading $cgdburl");
				}
			}
		}
	}
}
