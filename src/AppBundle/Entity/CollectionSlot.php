<?php

namespace AppBundle\Entity;

/**
 * CollectionSlot
 */
class CollectionSlot
{
    /**
     * @var integer
     */
    private $id;


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
     * @var integer
     */
    private $dice;

    /**
     * @var \AppBundle\Entity\Collection
     */
    private $collection;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $card;


    /**
     * Set dice
     *
     * @param integer $dice
     *
     * @return CollectionSlot
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
     * Set collection
     *
     * @param \AppBundle\Entity\Collection $collection
     *
     * @return CollectionSlot
     */
    public function setCollection(\AppBundle\Entity\Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return \AppBundle\Entity\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return CollectionSlot
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
    private $quantity;


    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return CollectionSlot
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
}
