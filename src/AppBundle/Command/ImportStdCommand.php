<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Type;
use AppBundle\Entity\Set;
use AppBundle\Entity\Card;
use AppBundle\Entity\Side;
use AppBundle\Entity\StarterPack;
use AppBundle\Entity\StarterPackSlot;

class ImportStdCommand extends ContainerAwareCommand
{
	/* @var $em EntityManager */
	private $em;

	/* @var $output OutputInterface */
	private $output;
	
	private $collections = [];

	protected function configure()
	{
		$this
		->setName('app:import:std')
		->setDescription('Import cards data file in json format from a copy of https://github.com/Alsciende/thronesdb-json-data')
		->addArgument(
				'path',
				InputArgument::REQUIRED,
				'Path to the repository'
				)
		
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $input->getArgument('path');
		$this->em = $this->getContainer()->get('doctrine')->getEntityManager();
		$this->output = $output;

		/* @var $helper \Symfony\Component\Console\Helper\QuestionHelper */
		$helper = $this->getHelper('question');

		// affiliations
		
		$output->writeln("Importing Affiliations...");
		$affiliationsFileInfo = $this->getFileInfo($path, 'affiliations.json');
		$imported = $this->importAffiliationsJsonFile($affiliationsFileInfo);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Affiliation');
		$output->writeln("Done.");

		// factions
		
		$output->writeln("Importing Factions...");
		$factionsFileInfo = $this->getFileInfo($path, 'factions.json');
		$imported = $this->importFactionsJsonFile($factionsFileInfo);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Faction');
		$output->writeln("Done.");
		
		// types
		
		$output->writeln("Importing Types...");
		$typesFileInfo = $this->getFileInfo($path, 'types.json');
		$imported = $this->importTypesJsonFile($typesFileInfo);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Type');
		$output->writeln("Done.");
		
		// subtypes
		
		$output->writeln("Importing Subtypes...");
		$subtypesFileInfo = $this->getFileInfo($path, 'subtypes.json');
		$imported = $this->importSubtypesJsonFile($subtypesFileInfo);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Subtype');
		$output->writeln("Done.");
		
		// rarities
		
		$output->writeln("Importing Rarities...");
		$raritiesFileInfo = $this->getFileInfo($path, 'rarities.json');
		$imported = $this->importRaritiesJsonFile($raritiesFileInfo);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Rarity');
		$output->writeln("Done.");

		// side types
		
		$output->writeln("Importing SideTypes...");
		$sideTypesFileInfo = $this->getFileInfo($path, 'sideTypes.json');
		$imported = $this->importSideTypesJsonFile($sideTypesFileInfo);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('SideType');
		$output->writeln("Done.");
		
		// second, sets

		$output->writeln("Importing Sets...");
		$setsFileInfo = $this->getFileInfo($path, 'sets.json');
		$imported = $this->importSetsJsonFile($setsFileInfo);
		$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Set');
		$output->writeln("Done.");
				
		// third, cards
		
		$output->writeln("Importing Cards...");
		$fileSystemIterator = $this->getFileSystemIterator($path);
		$imported = [];
		foreach ($fileSystemIterator as $fileinfo) {
			$imported = array_merge($imported, $this->importCardsJsonFile($fileinfo));
		}
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$this->loadCollection('Card');
		$output->writeln("Done.");
		
		$output->writeln("Importing Starter Packs...");
		$starterPacksFileInfo = $this->getFileInfo($path, 'starterPacks.json');
		$imported = $this->importStarterPacksJsonFile($starterPacksFileInfo);
		$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
		if(count($imported)) {
			$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
			if(!$helper->ask($input, $output, $question)) {
				die();
			}
		}
		$this->em->flush();
		$output->writeln("Done.");
	}

	protected function importAffiliationsJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$affiliation = $this->getEntityFromData('AppBundle\\Entity\\Affiliation', $data, [
					'code',
					'name',
					'is_primary'
			], [], []);
			if($affiliation) {
				$result[] = $affiliation;
				$this->em->persist($affiliation);
			}
		}
	
		return $result;
	}

	protected function importFactionsJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$faction = $this->getEntityFromData('AppBundle\\Entity\\Faction', $data, [
					'code',
					'name',
					'is_primary'
			], [], []);
			if($faction) {
				$result[] = $faction;
				$this->em->persist($faction);
			}
		}
	
		return $result;
	}
	
	protected function importTypesJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$type = $this->getEntityFromData('AppBundle\\Entity\\Type', $data, [
					'code',
					'name'
			], [], []);
			if($type) {
				$result[] = $type;
				$this->em->persist($type);
			}
		}
	
		return $result;
	}

	protected function importSubtypesJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$type = $this->getEntityFromData('AppBundle\\Entity\\Subtype', $data, [
					'code',
					'name'
			], [], []);
			if($type) {
				$result[] = $type;
				$this->em->persist($type);
			}
		}
	
		return $result;
	}

	protected function importRaritiesJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$rarity = $this->getEntityFromData('AppBundle\\Entity\\Rarity', $data, [
					'code',
					'name'
			], [], []);
			if($rarity) {
				$result[] = $rarity;
				$this->em->persist($rarity);
			}
		}
	
		return $result;
	}

	protected function importSideTypesJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$sideType = $this->getEntityFromData('AppBundle\\Entity\\SideType', $data, [
					'code',
					'icon',
					'name'
			], [], []);
			if($sideType) {
				$result[] = $sideType;
				$this->em->persist($sideType);
			}
		}
	
		return $result;
	}
	
	protected function importSetsJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$setsData = $this->getDataFromFile($fileinfo);
		foreach($setsData as $setData) {
			$set = $this->getEntityFromData('AppBundle\Entity\Set', $setData, [
					'code', 
					'name', 
					'position', 
					'size', 
					'date_release'
			], [], []);
			if($set) {
				$result[] = $set;
				$this->em->persist($set);
			}
		}
		
		return $result;
	}
	
	protected function importCardsJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$code = $fileinfo->getBasename('.json');
		
		$set = $this->em->getRepository('AppBundle:Set')->findOneBy(['code' => $code]);
		if(!$set) throw new \Exception("Unable to find Pack [$code]");
		
		$cardsData = $this->getDataFromFile($fileinfo);
		foreach($cardsData as $cardData) {
			$card = $this->getEntityFromData('AppBundle\Entity\Card', $cardData, [
					'code',
					'deck_limit',
					'position',
					'name',
					'has_die',
					'is_unique'
			], [
					'affiliation_code',
					'faction_code',
					'set_code',
					'rarity_code',
					'type_code',
					'subtype_code'
			], [
					'illustrator',
					'flavor',
					'text',
					'cost',
					'subtitle'
			]);
			if($card) {
				$result[] = $card;
				$this->em->persist($card);
			}
		}
		
		return $result;
	}

	protected function importStarterPacksJsonFile(\SplFileInfo $fileinfo)
	{
		$result = [];
	
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$starterPack = $this->getEntityFromData('AppBundle\\Entity\\StarterPack', $data, [
					'code',
					'name'
			], ['set_code'], []);
			if($starterPack) {
				$result[] = $starterPack;
				$this->em->persist($starterPack);
			}
		}
	
		return $result;
	}
	
	protected function copyFieldValueToEntity($entity, $entityName, $fieldName, $newJsonValue)
	{
		$metadata = $this->em->getClassMetadata($entityName);
		$type = $metadata->fieldMappings[$fieldName]['type'];
		
		// new value, by default what json gave us is the correct typed value
		$newTypedValue = $newJsonValue;
		
		// current value, by default the json, serialized value is the same as what's in the entity
		$getter = 'get'.ucfirst($fieldName);
		$currentJsonValue = $currentTypedValue = $entity->$getter();

		// if the field is a data, the default assumptions above are wrong
		if(in_array($type, ['date', 'datetime'])) {
			if($newJsonValue !== null) {
				$newTypedValue = new \DateTime($newJsonValue);				
			}
			if($currentTypedValue !== null) {
				switch($type) {
					case 'date': {
						$currentJsonValue = $currentTypedValue->format('Y-m-d');
						break;
					}
					case 'datetime': {
						$currentJsonValue = $currentTypedValue->format('Y-m-d H:i:s');
					}
				}
			}
		}
		
		$different = ($currentJsonValue !== $newJsonValue);
		if($different) {
			$this->output->writeln("Changing the <info>$fieldName</info> of <info>".$entity->toString()."</info> ($currentJsonValue => $newJsonValue)");
			$setter = 'set'.ucfirst($fieldName);
			$entity->$setter($newTypedValue);
		}
	}
	
	protected function copyKeyToEntity($entity, $entityName, $data, $key, $isMandatory = TRUE)
	{
		$metadata = $this->em->getClassMetadata($entityName);
		
		if(!key_exists($key, $data)) {
			if($isMandatory) {
				throw new \Exception("Missing key [$key] in ".json_encode($data));
			} else {
				$data[$key] = null;
			}
		}
		$value = $data[$key];
		
		if(!key_exists($key, $metadata->fieldNames)) {
			throw new \Exception("Missing column [$key] in entity ".$entityName);
		}
		$fieldName = $metadata->fieldNames[$key];
		
		$this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value);
	}

	protected function getEntityFromData($entityName, $data, $mandatoryKeys, $foreignKeys, $optionalKeys)
	{
		if(!key_exists('code', $data)) {
			throw new \Exception("Missing key [code] in ".json_encode($data));
		}
	
		$entity = $this->em->getRepository($entityName)->findOneBy(['code' => $data['code']]);
		if(!$entity) {
			$entity = new $entityName();
		}
		$orig = $entity->serialize();
	
		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($entity, $entityName, $data, $key, TRUE);
		}

		foreach($optionalKeys as $key) {
			$this->copyKeyToEntity($entity, $entityName, $data, $key, FALSE);
		}
		
		foreach($foreignKeys as $key) {
			$foreignEntityShortName = ucfirst(str_replace('_code', '', $key));
	
			if(!key_exists($key, $data)) {
				if($key=='subtype_code') continue;
				throw new \Exception("Missing key [$key] in ".json_encode($data));
			}

			$foreignCode = $data[$key];
			if(!key_exists($foreignEntityShortName, $this->collections)) {
				throw new \Exception("No collection for [$foreignEntityShortName] in ".json_encode($data));
			}
			if(!key_exists($foreignCode, $this->collections[$foreignEntityShortName])) {
				throw new \Exception("Invalid code [$foreignCode] for key [$key] in ".json_encode($data));
			}
			$foreignEntity = $this->collections[$foreignEntityShortName][$foreignCode];
	
			$getter = 'get'.$foreignEntityShortName;
			if(!$entity->$getter() || $entity->$getter()->getId() !== $foreignEntity->getId()) {
				$this->output->writeln("Changing the <info>$key</info> of <info>".$entity->toString()."</info>");
				$setter = 'set'.$foreignEntityShortName;
				$entity->$setter($foreignEntity);
			}
		}
	
		// special case for Card
		if($entityName === 'AppBundle\Entity\Card') {
			// calling a function whose name depends on the type_code
			$functionName = 'import' . $entity->getType()->getName() . 'Data';
			$this->$functionName($entity, $data);

			$this->importCardDieSides($entity, $data);
		}

		// special case for StarterPack
		if($entityName === 'AppBundle\Entity\StarterPack') {
			$this->importStarterPacksSlots($entity, $data);
		}
	
		if($entity->serialize() !== $orig) return $entity;
	}

	protected function importCardDieSides(Card $card, $data)
	{
		if($card->getHasDie())
		{
			if(!key_exists('sides', $data))
				throw new \Exception('Card ['.$card->getName().'] has die but no key [sides]');

			if(count($data['sides']) != 6)
				throw new \Exception('Card ['.$card->getName().'] has die but there are not 6 elements in key [sides]');

			foreach($data['sides'] as $index=>$sideData)
			{
				$side = NULL;
				if($index >= count($card->getSides()))
				{
					$side = new Side();
					$side->setCard($card);
					$card->addSide($side);
				}
				else
				{
					$side = $card->getSides()[$index];
				}
				
				$orig = $side->toString();

				preg_match('/^([-+]?)(\d*?)([-A-Z][a-zA-Z]?)(\d*?)$/', $sideData, $result);
				list($all, $modifier, $value, $type, $cost) = $result;

				if(!key_exists($type, $this->collections['SideType']))
					throw new \Exception("There is no side type with code [$type]");
				
				$side->setModifier($modifier=='+' ? 1 : $modifier=='-' ? -1 : 0);
				$side->setValue($value);
				$side->setType($this->collections['SideType'][$type]);
				$side->setCost($cost);

				if($orig !== $side->toString())
					$this->output->writeln("Changing the <info>side #".($index+1)."</info> of <info>".$card->toString()."</info> (".$orig." => ".$side->toString().")");
			}
		}
	}
	
	protected function importStarterPacksSlots(StarterPack $starter, $data)
	{
		$orig = $starter->getSlots()->getContent();

		foreach($data['slots'] as $code => $qtys) {
			if(!key_exists('Card', $this->collections)) {
				throw new \Exception("No collection for ['Card'] in ".json_encode($data));
			}
			if(!key_exists($code, $this->collections['Card'])) {
				throw new \Exception("Invalid code [$code] for key [slots] in ".json_encode($data));
			}

			$slot = $starter->getSlots()->getSlotByCode($code);
			if($slot==NULL) {
				$card = $this->collections['Card'][$code];
				$slot = new StarterPackSlot();
				$slot->setStarterPack($starter)->setCard($card);
				$starter->addSlot($slot);
			}
			$slot->setQuantity($qtys['quantity'])->setDice($qtys['dice']);
		}

		if($starter->getSlots()->getContent() !== $orig)
			$this->output->writeln("Changing <info>slots</info> of <info>".$starter->toString()."</info>");
	}

	protected function importBattlefieldData(Card $card, $data)
	{
		$mandatoryKeys = [
		];
		
		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
		}
	}

	protected function importSupportData(Card $card, $data)
	{
		$mandatoryKeys = [
				'cost'
		];

		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
		}
	}

	protected function importUpgradeData(Card $card, $data)
	{
		$mandatoryKeys = [
				'cost'
		];

		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
		}
	}

	protected function importCharacterData(Card $card, $data)
	{
		$mandatoryKeys = [
				'health',
				'points'
		];

		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
		}
	}

	protected function importEventData(Card $card, $data)
	{
		$mandatoryKeys = [
				'cost'
		];

		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
		}
	}

	protected function getDataFromFile(\SplFileInfo $fileinfo)
	{
	
		$file = $fileinfo->openFile('r');
		$file->setFlags(\SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
	
		$lines = [];
		foreach($file as $line) {
			if($line !== false) $lines[] = $line;
		}
		$content = implode('', $lines);
	
		$data = json_decode($content, true);
	
		if($data === null) {
			throw new \Exception("File [".$fileinfo->getPathname()."] contains incorrect JSON (error code ".json_last_error().")");
		}
	
		return $data;
	}
	
	protected function getFileInfo($path, $filename)
	{
		$fs = new Filesystem();
		
		if(!$fs->exists($path)) {
			throw new \Exception("No repository found at [$path]");
		}
		
		$filepath = "$path/$filename";
		
		if(!$fs->exists($filepath)) {
			throw new \Exception("No $filename file found at [$path]");
		}
		
		return new \SplFileInfo($filepath);
	}
	
	protected function getFileSystemIterator($path)
	{
		$fs = new Filesystem();
		
		if(!$fs->exists($path)) {
			throw new \Exception("No repository found at [$path]");
		}
		
		$directory = 'set';
		
		if(!$fs->exists("$path/$directory")) {
			throw new \Exception("No '$directory' directory found at [$path]");
		}
		
		$iterator = new \GlobIterator("$path/$directory/*.json");
		
		if(!$iterator->count()) {
			throw new \Exception("No json file found at [$path/set]");
		}
		
		return $iterator;
	}
	
	protected function loadCollection($entityShortName)
	{
		$this->collections[$entityShortName] = [];

		$entities = $this->em->getRepository('AppBundle:'.$entityShortName)->findAll();
		
		foreach($entities as $entity) {
			$this->collections[$entityShortName][$entity->getCode()] = $entity;
		}
	}
	
}