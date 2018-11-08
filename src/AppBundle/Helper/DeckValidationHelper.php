<?php 

namespace AppBundle\Helper;
use AppBundle\Model\SlotCollectionDecorator;
use AppBundle\Model\SlotCollectionProviderInterface;

use function Functional\some;
use function Functional\every;
use function Functional\none;

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
		if(!$this->withinFormatSets($card, $deck->getFormat())) {
			return false;
		}

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

		// Bo-Katan Kryze (WotF #89) special case
		if($deck->getSlots()->getSlotByCode('07089') != NULL) {
			if(    $card->getAffiliation()->getCode()==='villain' 
				&& $card->getFaction()->getCode()==='yellow' 
				&& $card->getType()->getCode()==='upgrade')
			{
				return true;
			}
		}

		// Leia Organa (AtG #90) special case
		if($deck->getSlots()->getSlotByCode('08090') != NULL) {
			if($card->getAffiliation()->getCode()==='villain')
			{
				return true;
			}
		}

		// Qi'Ra (AtG #135) special case
		if($deck->getSlots()->getSlotByCode('08135') != NULL) {
			if(    $card->getFaction()->getCode()==='yellow' 
				&& $card->getType()->getCode()==='event')
			{
				return true;
			}
		}

		return false;
	}

	public function withinFormatSets($card, $format) {
		if(in_array($card->getSet()->getCode(), $format->getData()["sets"]))
			return true;

		if($card->getReprints() !== NULL) {
			foreach($card->getReprints() as $reprint) {
				if(in_array($reprint->getSet()->getCode(), $format->getData()["sets"]))
					return true;
			}
		}

		if($card->getReprintOf() !== NULL) {
			if(in_array($card->getReprintOf()->getSet()->getCode(), $format->getData()["sets"]))
				return true;
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

	public function checkPlots(SlotCollectionProviderInterface $deck)
	{
		return every($deck->getSlots()->getPlotDeck(), function($slot) use($deck) {
			switch($slot->getCard()->getCode()) {
				//Retribution (AtG 54)
				case '08054':
					return some($deck->getSlots()->getCharacterDeck(), function($slot) {
						$card = $slot->getCard();
						$points = 0;
						if($card->getIsUnique())
						{
							$pointValues = preg_split('/\//', $card->getPoints());
							$points = intval($pointValues[$slot->getDice()-1], 10);
						}
						else
						{
							$points = intval($card->getPoints());
						}
						return $points >= 20;
					});
				//No Allegiance (AtG 155)
				case '08155':
					return none($deck->getSlots()->getCharacterDeck(), function($slot) {
						return in_array($slot->getCard()->getAffiliation()->getCode(), ["villain", "hero"]);
					});
				//Solidarity (AtG 156)
				case '08156':
					if(count($deck->getSlots()->getCharacterDeck()->getFactions()) > 1)
						return false;

					if(some($deck->getSlots()->getDrawDeck()->getContent(), function($slot, $code, $slots) {
						return max($slot["quantity"], $slot["dice"]) > 1;
					}))
						return false;

					return true;
				default:
					return true;
			}
		});
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

		if(count($deck->getSlots()->getBattlefieldDeck()) > ($deck->getSlots()->getSlotByCode('07127') != NULL ? 2 : 1)) {
			return 'too_many_battlefields';
		}

		if(!$this->checkPlots($deck)) {
			return 'plot';
		}

		$maxLimitExceeded = $deck->getSlots()->isSlotIncluded("08143") ? 2 : 0;
		$limitExceeded = 0;
		foreach($deck->getSlots()->getCopiesAndDeckLimit() as $cardName => $value) {
			if($value['deck_limit'] && ($value['copies'] - $value['deck_limit']) > 1) return 'too_many_copies';
			if($value['deck_limit'] && $value['copies'] > $value['deck_limit']) $limitExceeded++;
			if($limitExceeded > $maxLimitExceeded)
				return 'too_many_copies';
		}

		if($deck->getSlots()->isSlotIncluded('08090') && $deck->getSlots()->getCountByAffiliation()["villain"] > 5)
			return 'too_many_copies';

		if(!empty($this->getInvalidCards($deck))) {
			return 'invalid_cards';
		}
		
		if(!empty($this->getNotMatchingCards($deck))) {
			return 'faction_not_included';
		}

		return null;
	}	
}