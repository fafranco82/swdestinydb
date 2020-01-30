<?php

namespace AppBundle\Model;


/**
 * Interface for a collection of SlotInterface 
 */
interface SlotCollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
	/**
	 * Get quantity of cards
	 * @return integer
	 */
	public function countCards();
	
	/**
	 * Get included sets
	 * @return \AppBundle\Entity\Set[]
	 */
	public function getIncludedSets();
	
	/**
	 * Get a slot by card code
	 * @return array
	 */
	public function getSlotByCode($code);

	/**
	 * 
	 * @return boolean
	 */
	public function isSlotIncluded($code);

	/**
	 * Get all slots sorted by type code
	 * @return array
	 */
	public function getSlotsByType();

	/**
	 * Get all slots sorted by affiliation code
	 * @return array
	 */
	public function getSlotsByAffiliation();
	
	/**
	 * Get all slot counts sorted by type code
	 * @return array
	 */
	public function getCountByType();
	
	/**
	 * Get all slot counts sorted by faction code
	 * @return array
	 */
	public function getCountByFaction();

	/**
	 * Get all slot counts sorted by affiliation code
	 * @return array
	 */
	public function getCountByAffiliation();

	/**
	 * Get battlefield(s) as slots
	 * @return \AppBundle\Model\SlotCollectionInterface
	 */
	public function getBattlefieldDeck();
	
	/**
	 * Get the draw deck
	 * @return \AppBundle\Model\SlotCollectionInterface
	 */
	public function getDrawDeck();

	/**
	 * Get character row info (with non-unique character repeated)
	 * @return \AppBundle\Model\SlotCollectionInterface
	 */
	public function getCharacterRow();

	/**
	 * Get the character deck
	 * @return \AppBundle\Model\SlotCollectionInterface
	 */
	public function getCharacterDeck();

	/**
	 * Get character points
	 * @return integer
	 */
	public function getCharacterPoints();

	/**
	 * Get the character deck
	 * @return \AppBundle\Model\SlotCollectionInterface
	 */
	public function getPlotDeck();

	/**
	 * Get character points
	 * @return integer
	 */
	public function getPlotPoints();

	/**
	 * Get factions in an array (colors)
	 * @return array
	 */
	public function getFactions();

	
	/**
	 * Get the content as an array card_code => qty
	 * @return array
	 */
	public function getContent();
}
