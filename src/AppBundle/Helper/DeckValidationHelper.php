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
	
	public function findProblem(SlotCollectionProviderInterface $deck)
	{
		if($deck->getSlots()->getDrawDeck()->countCards() != 30) {
			return 'incorrect_size';
		}

		if($deck->getSlots()->getCharacterPoints() > 30) {
			return 'too_many_character_points';
		}

		foreach($deck->getSlots()->getCopiesAndDeckLimit() as $cardName => $value) {
			if($value['deck_limit'] && $value['copies'] > $value['deck_limit']) return 'too_many_copies';
		}

		if(!empty($this->getInvalidCards($deck))) {
			return 'invalid_cards';
		}
		
		/*
		$characterFactions = $deck->getSlots()->getCharacterDeck()->getFactions();
		$drawDeckFactions = $deck->getSlots()->getDrawDeck()->getFactions();
		$diff = array_diff($drawDeckFactions, $characterFactions);
		if(!(count($diff) == 0 || (count($diff) == 1 && $diff[0]=='gray'))) return 'faction_not_included';
		*/		

		return null;
	}	
}