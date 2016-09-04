<?php

namespace AppBundle\Entity;

/**
 * StarterPackSlot
 */
class StarterPackSlot
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
    private $quantity;

    /**
     * @var integer
     */
    private $dice;

    /**
     * @var \AppBundle\Entity\StarterPack
     */
    private $starterPack;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $card;


    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return StarterPackSlot
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
     * Set dice
     *
     * @param integer $dice
     *
     * @return StarterPackSlot
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
     * Set starterPack
     *
     * @param \AppBundle\Entity\StarterPack $starterPack
     *
     * @return StarterPackSlot
     */
    public function setStarterPack(\AppBundle\Entity\StarterPack $starterPack = null)
    {
        $this->starterPack = $starterPack;

        return $this;
    }

    /**
     * Get starterPack
     *
     * @return \AppBundle\Entity\StarterPack
     */
    public function getStarterPack()
    {
        return $this->starterPack;
    }

    /**
     * Set card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return StarterPackSlot
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
}
