<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Deckslot;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Entity\Deckchange;
use AppBundle\Helper\DeckValidationHelper;
use AppBundle\Helper\AgendaHelper;

class Decks
{
	public function __construct(EntityManager $doctrine, DeckValidationHelper $deck_validation_helper, AgendaHelper $agenda_helper, Diff $diff, Logger $logger)
	{
		$this->doctrine = $doctrine;
		$this->deck_validation_helper = $deck_validation_helper;
		$this->agenda_helper = $agenda_helper;
		$this->diff = $diff;
		$this->logger = $logger;
	}

	public function getByUser($user, $decode_variation = FALSE)
	{
		$decks = $user->getDecks();
		$list = [];
		foreach($decks as $deck) {
			$list[] = $deck->jsonSerialize(false);
		}

		return $list;
	}

	/**
	 *
	 * @param unknown $user
	 * @param Deck $deck
	 * @param unknown $decklist_id
	 * @param unknown $name
	 * @param unknown $affiliation
	 * @param unknown $format
	 * @param unknown $description
	 * @param unknown $tags
	 * @param unknown $content
	 * @param unknown $source_deck
	 */
	public function saveDeck($user, $deck, $decklist_id, $name, $affiliation, $format, $description, $tags, $content, $source_deck)
	{
		$deck_content = [ ];

		if ($decklist_id) {
			$decklist = $this->doctrine->getRepository('AppBundle:Decklist')->find($decklist_id);
			if ($decklist)
				$deck->setParent($decklist);
		}

		$deck->setName($name);
		$deck->setAffiliation($affiliation);
		$deck->setFormat($format);
		$deck->setDescriptionMd($description);
		$deck->setUser($user);
		$deck->setMinorVersion($deck->getMinorVersion() + 1);
		$cards = [];
		/* @var $latestSet \AppBundle\Entity\Set */
		$latestSet = null;
		foreach($content as $card_code => $qtys) {
			$card = $this->doctrine->getRepository('AppBundle:Card')->findOneBy(array(
					"code" => $card_code
			) );
			if(!$card)
				continue;
			$set = $card->getSet();
			if(!$latestSet) {
				$latestSet = $set;
			} else if ($latestSet->getPosition() < $set->getPosition()) {
				$latestSet = $set;
			}
			$cards[$card_code] = $card;
		}
		$deck->setLastSet($latestSet);
		if(empty($tags)) {
			// tags can never be empty. if it is we put affiliation in
			$tags = [$affiliation->getCode()];
		}
		if(is_string($tags)) 
		{
			$tags = preg_split('/\s+/', $tags);
		}
		$tags = implode(' ', array_unique(array_values($tags)));
		$deck->setTags($tags);
		$this->doctrine->persist($deck);

		// on the deck content
		if ($source_deck) {
			// compute diff between current content and saved content
			list($listings) = $this->diff->diffContents(array(
				$content,
				$source_deck->getSlots()->getContent()
			));
			// remove all change (autosave) since last deck update (changes are sorted)
			$changes = $this->getUnsavedChanges($deck);
			foreach($changes as $change) {
				$this->doctrine->remove($change);
			}
			$this->doctrine->flush();
			// save new change unless empty
			if (count($listings[0]) || count($listings[1])) {
				$change = new Deckchange();
				$change->setDeck($deck);
				$change->setVariation(json_encode($listings));
				$change->setIsSaved(TRUE);
				$change->setVersion($deck->getVersion());
				$this->doctrine->persist($change);
				$this->doctrine->flush();
			}

			// copy version
			$deck->setMajorVersion($source_deck->getMajorVersion());
			$deck->setMinorVersion($source_deck->getMinorVersion());
		}

		foreach ($deck->getSlots() as $slot) {
			$deck->removeSlot($slot);
			$this->doctrine->remove($slot);
		}

		foreach ($content as $card_code => $qtys) {
			$qty = $qtys['quantity'];
			$dice = $qtys['dice'];
			$dices = $qtys['dices'];
			
			$card = $cards[$card_code];
			$slot = new Deckslot();
			$slot->setQuantity($qty);
			$slot->setDice($dice);
			$slot->setDices($dices);
			$slot->setCard($card);
			$slot->setDeck($deck);
			$deck->addSlot($slot);
			$deck_content[$card_code] = array (
				'card' => $card,
				'qty' => $qty,
				'dice' => $dice
			);
		}

		$deck->setProblem($this->deck_validation_helper->findProblem($deck));

		return $deck->getId();
	}

	public function revertDeck($deck)
	{
		$changes = $this->getUnsavedChanges ( $deck );
		foreach ( $changes as $change ) {
			$this->doctrine->remove ( $change );
		}
		// if deck has only one card and it's an agenda, we delete it
		if(count($deck->getSlots()) === 0 || (
			count($deck->getSlots()) === 1 && $deck->getSlots()->getAgenda()
		) ) {
			$this->doctrine->remove($deck);
		}
		$this->doctrine->flush ();
	}

	public function getUnsavedChanges($deck)
	{
		return $this->doctrine->getRepository ( 'AppBundle:Deckchange' )->findBy ( array (
				'deck' => $deck,
				'isSaved' => FALSE
		) );
	}
}
