<?php 

namespace AppBundle\Helper;
use AppBundle\Model\SlotCollectionDecorator;
use AppBundle\Model\SlotCollectionProviderInterface;

class DeckValidationHelper
{
	
	public function __construct()
	{
		
	}
	
	public function getInvalidCards($deck)
	{
		$invalidCards = [];
		foreach ( $deck->getSlots() as $slot ) {
			if(! $this->canIncludeCard($deck, $slot->getCard())) {
				$invalidCards[] = $slot->getCard();
			}
		}
		return $invalidCards;
	}

	public function getNotMatchingCards($deck)
	{
		$notMatchingCards = [];
		foreach ( $deck->getSlots() as $slot ) {
			if(! $this->spotCharacterFaction($deck, $slot->getCard())) {
				$notMatchingCards[] = $slot->getCard();
			}
		}
		return $notMatchingCards;
	}
	
	public function canIncludeCard(SlotCollectionProviderInterface $deck, $card) {
		if($card->getAffiliation()->getCode() === 'neutral') {
			return true;
		}

		if($card->getAffiliation()->getCode() === $deck->getAffiliation()->getCode()) {
			return true;
		}

		// Finn (AW #45) special case
		if($deck->getSlots()->getSlotByCode('01045') != NULL) {
			if(    $card->getAffiliation()->getCode()==='villain' 
				&& $card->getFaction()->getCode()==='red' 
				&& in_array($card->getSubtype()->getCode(), array('vehicle', 'weapon')))
			{
				return true;
			}
		}

		return false;
	}

	public function spotCharacterFaction(SlotCollectionProviderInterface $deck, $card) {
		$factions = $deck->getSlots()->getCharacterDeck()->getFactions();

		if($card->getFaction()->getCode() === 'gray' || in_array($card->getFaction()->getCode(), $factions)) {
			return true;
		}

		// Finn (AW #45) special case
		if($deck->getSlots()->getSlotByCode('01045') != NULL) {
			if(    $card->getAffiliation()->getCode()==='villain' 
				&& $card->getFaction()->getCode()==='red' 
				&& in_array($card->getSubtype()->getCode(), array('vehicle', 'weapon')))
			{
				return true;
			}
		}

		return false;
	}
	
	public function findProblem(SlotCollectionProviderInterface $deck)
	{
		if($deck->getSlots()->getDrawDeck()->countCards() != 30) {
			return 'incorrect_size';
		}

		if($deck->getSlots()->getCharacterPoints()+$deck->getSlots()->getPlotPoints() > 30) {
			return 'too_many_points';
		}

		if(count($deck->getSlots()->getBattlefieldDeck()) == 0) {
			return 'no_battlefield';
		}

		foreach($deck->getSlots()->getCopiesAndDeckLimit() as $cardName => $value) {
			if($value['deck_limit'] && $value['copies'] > $value['deck_limit']) return 'too_many_copies';
		}

		if(!empty($this->getInvalidCards($deck))) {
			return 'invalid_cards';
		}
		
		if(!empty($this->getNotMatchingCards($deck))) {
			return 'faction_not_included';
		}

		return null;
	}	
}