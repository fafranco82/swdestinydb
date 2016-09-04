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
	
	public function getSlotsByType() {
		$slotsByType = [ 'battlefield' => [], 'character' => [], 'upgrade' => [], 'support' => [], 'event' => [] ];
		foreach($this->slots as $slot) {
			if(array_key_exists($slot->getCard()->getType()->getCode(), $slotsByType)) {
				$slotsByType[$slot->getCard()->getType()->getCode()][] = $slot;
			}
		}
		return $slotsByType;
	}
	
	public function getCountByType() {
		$countByType = [ 'location' => 0, 'attachment' => 0, 'event' => 0 ];
		foreach($this->slots as $slot) {
			if(array_key_exists($slot->getCard()->getType()->getCode(), $countByType)) {
				$countByType[$slot->getCard()->getType()->getCode()] += $slot->getQuantity();
			}
		}
		return $countByType;
	}

	public function getDrawDeck()
	{
		$drawDeck = [];
		foreach($this->slots as $slot) {
			if($slot->getCard()->getType()->getCode() === 'upgrade'
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
		foreach($this->slots as $slot) {
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
