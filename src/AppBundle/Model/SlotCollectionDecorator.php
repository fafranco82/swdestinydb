<?php

namespace AppBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Format;
use AppBundle\Entity\Deckslot;
use AppBundle\Entity\Decklistslot;

/**
 * Decorator for a collection of SlotInterface 
 */
class SlotCollectionDecorator implements \AppBundle\Model\SlotCollectionInterface
{
	protected $slots;
	
	public function __construct(\Doctrine\Common\Collections\Collection $slots, Format $format = NULL)
	{
		$this->slots = $slots;
		$this->format = $format;
	}
	
	public function add($element)
	{
		return $this->slots->add($element);
	}

	public function removeElement($element)
	{
		return $this->slots->removeElement($element);
	}
	
	public function count($mode = null)
	{
		return $this->slots->count($mode);
	}
	
	public function getIterator()
	{
		return $this->slots->getIterator();
	}
	
	public function offsetExists($offset)
	{
		return $this->slots->offsetExists($offset);
	}
	
	public function offsetGet($offset)
	{
		return $this->slots->offsetGet($offset);
	}
	
	public function offsetSet($offset, $value)
	{
		return $this->slots->offsetSet($offset, $value);
	}
	
	public function offsetUnset($offset)
	{
		return $this->slots->offsetUnset($offset);
	}
	
	public function countCards() 
	{
		$count = 0;
		foreach($this->slots as $slot) {
			$count += $slot->getQuantity();
		}
		return $count;
	}
	
	public function getIncludedSets() {
		$sets = [];
		foreach ($this->slots as $slot) {
			$card = $slot->getCard();
			$set = $card->getSet();
			if(!isset($sets[$set->getPosition()])) {
				$sets[$set->getPosition()] = [
					'set' => $set,
					'nb' => 1
				];
			}
		}
		ksort($sets);
		return array_values($sets);
	}

	public function getSlotByCode($code) {
		foreach($this->slots as $slot) {
			if($slot->getCard()->getCode() == $code) {
				return $slot;
			}
		}
		return NULL;
	}

	public function isSlotIncluded($code) {
		$slot = $this->getSlotByCode($code);
		return $slot != NULL;
	}
	
	public function getSlotsByType() {
		$slotsByType = [ 'battlefield' => [], 'plot' => [], 'upgrade' => [], 'downgrade' => [], 'support' => [], 'event' => [] ];
		foreach($this->slots as $slot) {
			if(array_key_exists($slot->getCard()->getType()->getCode(), $slotsByType)) {
				$slotsByType[$slot->getCard()->getType()->getCode()][] = $slot;
			}
		}
		$slotsByType['character'] = $this->getCharacterArray();
		return $slotsByType;
	}

	public function getSlotsByAffiliation() {
		$getSlotsByAffiliation = [ 'villain' => [], 'hero' => [], 'neutral' => [] ];
		foreach($this->slots as $slot) {
			if(array_key_exists($slot->getCard()->getAffiliation()->getCode(), $getSlotsByAffiliation)) {
				$getSlotsByAffiliation[$slot->getCard()->getAffiliation()->getCode()][] = $slot;
			}
		}
		return $getSlotsByAffiliation;
	}
	
	public function getCountByType() {
		$countByType = [ 
			'upgrade' => array(
				"cards" => 0,
				"dice" => 0),

			'downgrade' => array(
				"cards" => 0,
				"dice" => 0),

			'support' => array(
				"cards" => 0,
				"dice" => 0),

			'event' => array(
				"cards" => 0,
				"dice" => 0)];

		foreach($this->slots as $slot) {
			$code = $slot->getCard()->getType()->getCode();
			if(array_key_exists($code, $countByType)) {
				$countByType[$code]["cards"] += $slot->getQuantity();
				$countByType[$code]["dice"] += $slot->getDice();
			}
		}
		return $countByType;
	}

	public function getCountByFaction() {
		$countByFaction = ['red' => 0, 'yellow' => 0, 'blue' => 0];

		foreach($this->slots as $slot) {
			$code = $slot->getCard()->getFaction()->getCode();
			if(array_key_exists($code, $countByFaction)) {
				$countByFaction[$code] += max($slot->getQuantity(), $slot->getDice());
			}
		}
		return $countByFaction;
	}

	public function getCountByAffiliation() {
		$countByAffiliation = ['villain' => 0, 'hero' => 0];

		foreach($this->slots as $slot) {
			$code = $slot->getCard()->getAffiliation()->getCode();
			if(array_key_exists($code, $countByAffiliation)) {
				$countByAffiliation[$code] += max($slot->getQuantity(), $slot->getDice());
			}
		}
		return $countByAffiliation;
	}

	public function getBattlefieldDeck()
	{
		$battlefieldDeck = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'battlefield') {
				$battlefieldDeck[] = $slot;
			}
		}
		return new SlotCollectionDecorator(new ArrayCollection($battlefieldDeck));
	}

	public function getDrawDeck()
	{
		$drawDeck = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'upgrade'
			|| $slot->getCard()->getType()->getCode() === 'downgrade'
			|| $slot->getCard()->getType()->getCode() === 'support'
			|| $slot->getCard()->getType()->getCode() === 'event') {
				$drawDeck[] = $slot;
			}
		}
		return new SlotCollectionDecorator(new ArrayCollection($drawDeck));
	}

	public function getCharacterDeck()
	{
		$characterDeck = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'character') {
				$characterDeck[] = $slot;
			}
		}
		return new SlotCollectionDecorator(new ArrayCollection($characterDeck));
	}

	public function getCharacterArray()
	{
		$characterRow = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'character') {
				if($slot->getCard()->getIsUnique()) {
					$characterRow[] = $slot;
				} else if(($slot instanceof Deckslot || $slot instanceof Decklistslot) && $slot->getDices()) {
					foreach(explode(",", $slot->getDices()) as $i) {
						$slot->setDice($i);
						$slot->setQuantity(1);
						$characterRow[] = clone $slot;
					}
				} else {
					$totalCards = $slot->getQuantity();
					$slot->setDice(1);
					$slot->setQuantity(1);
					for($i = 0; $i < $totalCards; $i++) {
						$characterRow[] = $slot;
					}
				}
			}
		}
		return $characterRow;
	}

	public function getCharacterRow()
	{
		return new SlotCollectionDecorator(new ArrayCollection($this->getCharacterArray()));
	}

	public function getCharacterPoints()
	{
		$points = 0;
		forEach($this->slots as $slot)
		{
			$card = $slot->getCard();
			if($card->getType()->getCode() != 'character') continue;

			$formatPoints = $card->getPoints();
			if($this->format && array_key_exists($card->getCode(), $this->format->getData()['balance']))
			{
				$formatPoints = $this->format->getData()["balance"][$card->getCode()];
			}

			$inc = 0;
			if(($slot instanceof Deckslot || $slot instanceof Decklistslot) && $slot->getDices()) {
				
				foreach(explode(",", $slot->getDices()) as $i) {
					$pointValues = preg_split('/\//', $formatPoints);
					$inc += intval($pointValues[$i-1], 10);
				}
			}
			else if($card->getIsUnique())
			{
				$pointValues = preg_split('/\//', $formatPoints);
				$inc = intval($pointValues[$slot->getDice()-1], 10);
			}
			else
			{
				$inc = intval($formatPoints) * $slot->getQuantity();
			}

			$points += $inc;
		};
		
		//if Clone Commander Cody (AtG #73)
		if($this->isSlotIncluded("08073")) {
			$points += $this->removeOneForOtherCharacterCodes(array('05038'), '08073');
		}

		//if General Grievous - Droid Armies Commander (CONV #21)
		if($this->isSlotIncluded('09021')) {
			$points += $this->removeOneForOtherCharacterSubtype('droid', '09021');
		}

		//if Kanan Jarrus - Jedi Exile (CONV #55)
		if($this->isSlotIncluded("12055")) {
			$points += $this->removeOneForOtherCharacterSubtype('spectre', '12055', false);
		}

		//if Luke Skywalker - Seeking The Path (TR #2A)
		if($this->isSlotIncluded("13002A")) {
			$points += $this->removeOneForOtherCharacterNames(array('Obi-Wan Kenobi','Yoda'), '13002A');
		}
		
		//if Closing In (TR #6A)
		if($this->isSlotIncluded("13006A")) {
			$points += $this->removeOneForOtherCharacterSubtype('bounty-hunter', '13006A');
		}
		
		//if Rescue Han Solo (TR #7A)
		if($this->isSlotIncluded("13007A")) {
			$points += $this->removeOneForOtherCharacterNames(array('Chewbacca','Lando Calrissian','Leia Organa','Luke Skywalker'), '13007A');
		}

		//if Rescue The Princess (EC #44B)
		if($this->isSlotIncluded("701044B")) {
			$points += $this->removeOneForOtherCharacterNames(array('Chewbacca','Han Solo','Luke Skywalker','Obi-Wan Kenobi'), '701044B');
		}
		
		return $points;
	}

	public function getPlotDeck()
	{
		$plotDeck = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'plot') {
				$plotDeck[] = $slot;
			}
		}
		return new SlotCollectionDecorator(new ArrayCollection($plotDeck));
	}

	public function getPlotPoints()
	{
		$points = 0;
		forEach($this->slots as $slot)
		{
			$card = $slot->getCard();
			if($card->getType()->getCode() != 'plot') continue;

			$points += intval($card->getPoints()) * $slot->getQuantity();
		};

		//if Director Krennic - Death Star Mastermind (CM #21)
		if($this->isSlotIncluded("12021")) {
			$points += $this->removeOneForPlotSubtype('death-star');
		}

		//if Luke Skywalker - Red Five (CM #56)
		if($this->isSlotIncluded("12056")) {
			$points += $this->removeOneForPlotSubtype('death-star');
		}

		return $points;
	}

	public function getFactions()
	{
		$factions = [];
		forEach($this->slots AS $slot)
		{
			$factions[] = $slot->getCard()->getFaction()->getCode();
		}
		return array_unique($factions);
	}
	
	public function getCopiesAndDeckLimit()
	{
		$copiesAndDeckLimit = [];
		foreach($this->getDrawDeck()->getSlots() as $slot) {
			$cardName = $slot->getCard()->getName();
			if(!key_exists($cardName, $copiesAndDeckLimit)) {
				$copiesAndDeckLimit[$cardName] = [
					'copies' => $slot->getQuantity(),
					'deck_limit' => $slot->getCard()->getDeckLimit(),
				];
			} else {
				$copiesAndDeckLimit[$cardName]['copies'] += $slot->getQuantity();
				$copiesAndDeckLimit[$cardName]['deck_limit'] = min($slot->getCard()->getDeckLimit(), $copiesAndDeckLimit[$cardName]['deck_limit']);
			}
		}
		return $copiesAndDeckLimit;
	}
	
	public function getSlots()
	{
		return $this->slots;
	}

	public function getContent()
	{
		$arr = array ();
		foreach ( $this->slots as $slot ) {
			if($slot instanceof Deckslot || $slot instanceof Decklistslot) {
				$arr [$slot->getCard()->getCode()] = array(
					"quantity" => $slot->getQuantity(),
					"dice" => $slot->getDice(),
					"dices" => $slot->getDices()
				);
			} else {
				$arr [$slot->getCard()->getCode()] = array(
					"quantity" => $slot->getQuantity(),
					"dice" => $slot->getDice()
				);
			}
		}
		ksort ( $arr );
		return $arr;
	}
	
	/** New methods for managing cards handlind point values **/

	protected function removeOneForOtherCharacterSubtype($subtype, $currentCard, $forEachOne = true) {
		return $this->removeOneForOtherSubtype($this->getCharacterDeck(), $subtype, $currentCard, $forEachOne);
	}
	
	protected function removeOneForPlotSubtype($subtype) {
		return $this->removeOneForOtherSubtype($this->getPlotDeck(), $subtype, '', false);
	}
	
	protected function removeOneForOtherSubtype($deck, $subtype, $currentCard, $forEachOne = true) {
		return $this->removeOneForFiltered($deck, $currentCard, $forEachOne, 
			function($card) use ($subtype) { 
				foreach ($card->getSubtypes() as $subtypeObject) {
					if($subtypeObject->getCode() == $subtype) {
						return true;
					}
				}
				return false; 
			}
		);
	}
	
	protected function removeOneForOtherCharacterCodes($characters, $currentCard, $forEachOne = true) {
		return $this->removeOneForFiltered($this->getCharacterDeck(), $currentCard, $forEachOne, 
			function($card) use ($characters) { return in_array($card->getCode(), $characters); }
		);
	}
	
	protected function removeOneForOtherCharacterNames($characters, $currentCard, $forEachOne = true) {
		return $this->removeOneForFiltered($this->getCharacterDeck(), $currentCard, $forEachOne, 
			function($card) use ($characters) { return in_array($card->getName(), $characters); }
		);
	}
	
	protected function removeOneForFiltered($deck, $cardCode, $forEachOne, $filter) {
		$points = 0;
		foreach($deck->getSlots() as $slot) {
			// Do not apply on self
			if($slot->getCard()->getCode() == $cardCode) {
				break;
			}
			if(call_user_func($filter, $slot->getCard())) {
				if($forEachOne) {
					$points -= $slot->getQuantity();
				} else {
					return -1;
				}
			}
		}
		return $points;
	}

}
