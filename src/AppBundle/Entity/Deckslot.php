<?php

namespace AppBundle\Entity;

class Deckslot implements \AppBundle\Model\SlotInterface
{
	
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var \AppBundle\Entity\Deck
     */
    private $deck;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $card;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Deckslot
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set deck
     *
     * @param \AppBundle\Entity\Deck $deck
     *
     * @return Deckslot
     */
    public function setDeck(\AppBundle\Entity\Deck $deck = null)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * Get deck
     *
     * @return \AppBundle\Entity\Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Set card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return Deckslot
     */
    public function setCard(\AppBundle\Entity\Card $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return \AppBundle\Entity\Card
     */
    public function getCard()
    {
        return $this->card;
    }
	
    /**
     * @var integer
     */
    private $dice;


    /**
     * Set dice
     *
     * @param integer $dice
     *
     * @return Deckslot
     */
    public function setDice($dice)
    {
        $this->dice = $dice;

        return $this;
    }

    /**
     * Get dice
     *
     * @return integer
     */
    public function getDice()
    {
        return $this->dice;
    }
	
	/**
	 * @var string
	 */
	private $dices;

	/**
	 * Set dices
	 *
	 * @param string $dices
	 *
	 * @return Deckslot
	 */
	public function setDices($dices)
	{
		$this->dices = $dices;

		return $this;
	}

	/**
	 * Get dices
	 *
	 * @return string
	 */
	public function getDices()
	{
		return $this->dices;
	}
}
