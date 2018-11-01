<?php

namespace AppBundle\Entity;

class Side
{
    public function toString() {
        $type = "*";
        if(!is_null($this->type))
            $type = $this->type->getCode();
        
        $s = "";
        
        if($this->modifier > 0)
        {
            $s = $s."+";
        }
        else if($this->modifier < 0)
        {
            $s = $s."-";
        }

        if($type != "-" && $type != "Sp")
        {
            if(is_null($this->value))
                $s = $s."X";
            else
                $s = $s.$this->value;
        }

        $s = $s.$type;

        if($this->cost > 0)
        {
            $s = $s.$this->cost;
        }
		return $s;
	}
	
	/**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $value;

    /**
     * @var integer
     */
    private $modifier;

    /**
     * @var integer
     */
    private $cost;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $card;

    /**
     * @var \AppBundle\Entity\SideType
     */
    private $type;    

    /**
     * Card card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return Side
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
     * Set type
     *
     * @param \AppBundle\Entity\SideType $type
     *
     * @return Side
     */
    public function setType(\AppBundle\Entity\SideType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\SideType
     */
    public function getType()
    {
        return $this->type;
    }

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
     * Set value
     *
     * @param integer $value
     *
     * @return Side
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set modifier
     *
     * @param integer $modifier
     *
     * @return Side
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * Get modifier
     *
     * @return integer
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return Side
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Side
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Side
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }
}
