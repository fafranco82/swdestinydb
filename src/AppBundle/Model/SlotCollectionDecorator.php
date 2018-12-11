<?php

namespace AppBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * Decorator for a collection of SlotInterface 
 */
class SlotCollectionDecorator implements \AppBundle\Model\SlotCollectionInterface
{
	protected $slots;
	
	public function __construct(\Doctrine\Common\Collections\Collection $slots)
	{
		$this->slots = $slots;
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
		$slotsByType = [ 'battlefield' => [], 'plot' => [], 'character' => [], 'upgrade' => [], 'downgrade' => [], 'support' => [], 'event' => [] ];
		foreach($this->slots as $slot) {
			if(array_key_exists($slot->getCard()->getType()->getCode(), $slotsByType)) {
				$slotsByType[$slot->getCard()->getType()->getCode()][] = $slot;
			}
		}
		return $slotsByType;
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

	public function getCharacterRow()
	{
		$characterRow = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'character') {
				if($slot->getCard()->getIsUnique()) {
					$characterRow[] = $slot;
				} else {
					$totalDice = $slot->getDice();
					$slot->setDice(1);
					$slot->setQuantity(1);
					for($i = 0; $i < $totalDice; $i++) {
						$characterRow[] = $slot;
					}
				}
			}
		}
		return new SlotCollectionDecorator(new ArrayCollection($characterRow));
	}

	public function getCharacterPoints()
	{
		$points = 0;
		forEach($this->slots as $slot)
		{
			$card = $slot->getCard();
			if($card->getType()->getCode() != 'character') continue;

			$inc = 0;
			if($card->getIsUnique())
			{
				$pointValues = preg_split('/\//', $card->getPoints());
				$inc = intval($pointValues[$slot->getDice()-1], 10);
			}
			else
			{
				$inc = intval($card->getPoints()) * $slot->getDice();
			}
			$points += $inc;
		};
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
			$arr [$slot->getCard()->getCode()] = array(
				"quantity" => $slot->getQuantity(),
				"dice" => $slot->getDice()
			);
		}
		ksort ( $arr );
		return $arr;
	}
	
}
