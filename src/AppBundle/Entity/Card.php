<?php

namespace AppBundle\Entity;

class Card implements \Gedmo\Translatable\Translatable, \Serializable
{
    private function snakeToCamel($snake) {
        $parts = explode('_', $snake);
        return implode('', array_map('ucfirst', $parts));
    }
    
	public function serialize() {
        $serialized = [];
        if(empty($this->code)) return $serialized;

        $mandatoryFields = [
                'code',
                'deck_limit',
                'position',
                'name',
                'is_unique',
                'has_die',
                'has_errata'
        ];
    
        $optionalFields = [
                'ttscardid',
                'illustrator',
                'flavor',
                'text',
                'cost',
                'subtitle',
                'flip_card'
        ];
    
        $externalFields = [
                'faction',
                'set',
                'type',
                'rarity',
                'affiliation'
        ];

        switch($this->type->getCode())
        {
            case 'character':
                $mandatoryFields[] = 'health';
                $mandatoryFields[] = 'points';
                break;
            case 'support':
            case 'upgrade':
            case 'event':
                $mandatoryFields[] = 'cost';
                break;
        }

        foreach($optionalFields as $optionalField) {
            $getter = 'get' . $this->snakeToCamel($optionalField);
            $serialized[$optionalField] = $this->$getter();
            if(!isset($serialized[$optionalField]) || $serialized[$optionalField] === '') unset($serialized[$optionalField]);
        }
    
        foreach($mandatoryFields as $mandatoryField) {
            $getter = 'get' . $this->snakeToCamel($mandatoryField);
            $serialized[$mandatoryField] = $this->$getter();
        }
    
        foreach($externalFields as $externalField) {
            $getter = 'get' . $this->snakeToCamel($externalField);
            $object = $this->$getter();
            if($object)
                $serialized[$externalField.'_code'] = $this->$getter()->getCode();
        }

        if(!empty($this->subtypes))
        {
            $serialized['subtypes'] = array();
            foreach($this->subtypes as $subtype)
            {
                $serialized['subtypes'][] = $subtype->getCode();
            }
        }

        if($this->hasDie)
        {
            $serialized['sides'] = array();
            foreach($this->sides as $side)
            {
                $serialized['sides'][] = $side->toString();
            }
        }
    
        ksort($serialized);

		return $serialized;
	}

	public function unserialize($serialized) {
		throw new \Exception("unserialize() method unsupported");
	}
	
    public function toString() {
		return $this->name;
	}
	
	/*
    * I18N vars
    */
    private $locale = 'en';

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $ttscardid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $cost;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $subtypes;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var integer
     */
    private $deckLimit;

    /**
     * @var string
     */
    private $flavor;

    /**
     * @var string
     */
    private $illustrator;

    /**
     * @var boolean
     */
    private $isUnique;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reviews;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sides;

    /**
     * @var \AppBundle\Entity\Set
     */
    private $set;

    /**
     * @var \AppBundle\Entity\Type
     */
    private $type;

    /**
     * @var \AppBundle\Entity\Faction
     */
    private $faction;

    /**
     * @var \AppBundle\Entity\Affiliation
     */
    private $affiliation;

    /**
     * @var \AppBundle\Entity\Rarity
     */
    private $rarity;

    /**
     * @var boolean
     */
    private $flipCard;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subtypes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sides = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set position
     *
     * @param integer $position
     *
     * @return Card
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Card
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set ttscardid
     *
     * @param string $code
     *
     * @return Card
     */
    public function setTtscardid($ttscardid)
    {
        $this->ttscardid = $ttscardid;

        return $this;
    }

    /**
     * Get ttscardid
     *
     * @return string
     */
    public function getTtscardid()
    {
        return $this->ttscardid;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Card
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return Card
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
     * Set text
     *
     * @param string $text
     *
     * @return Card
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set subtype
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $subtypes
     *
     * @return Card
     */
    public function setSubtypes(\Doctrine\Common\Collections\ArrayCollection $subtypes)
    {
        $this->subtypes = $subtypes;

        return $this;
    }

    /**
     * Get subtype
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubtypes()
    {
        return $this->subtypes;
    }

    public function addSubtype(Subtype $subtype)
    {
        $this->subtypes[] = $subtype;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Card
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
     * @return Card
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

    /**
     * Set deckLimit
     *
     * @param integer $deckLimit
     *
     * @return Card
     */
    public function setDeckLimit($deckLimit)
    {
        $this->deckLimit = $deckLimit;

        return $this;
    }

    /**
     * Get deckLimit
     *
     * @return integer
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * Set flavor
     *
     * @param string $flavor
     *
     * @return Card
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * Get flavor
     *
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Set illustrator
     *
     * @param string $illustrator
     *
     * @return Card
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;

        return $this;
    }

    /**
     * Get illustrator
     *
     * @return string
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * Set isUnique
     *
     * @param boolean $isUnique
     *
     * @return Card
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * Get isUnique
     *
     * @return boolean
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * Add review
     *
     * @param \AppBundle\Entity\Review $review
     *
     * @return Card
     */
    public function addReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \AppBundle\Entity\Review $review
     */
    public function removeReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Add side
     *
     * @param \AppBundle\Entity\Side $side
     *
     * @return Card
     */
    public function addSide(\AppBundle\Entity\Side $side)
    {
        $this->sides[] = $side;

        return $this;
    }

    /**
     * Remove side
     *
     * @param \AppBundle\Entity\Side $side
     */
    public function removeSide(\AppBundle\Entity\Side $side)
    {
        $this->sides->removeElement($side);
    }

    /**
     * Get sides
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSides()
    {
        return $this->sides;
    }

    /**
     * Set set
     *
     * @param \AppBundle\Entity\Set $set
     *
     * @return Card
     */
    public function setSet(\AppBundle\Entity\Set $set = null)
    {
        $this->set = $set;

        return $this;
    }

    /**
     * Get set
     *
     * @return \AppBundle\Entity\Set
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\Type $type
     *
     * @return Card
     */
    public function setType(\AppBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set faction
     *
     * @param \AppBundle\Entity\Faction $faction
     *
     * @return Card
     */
    public function setFaction(\AppBundle\Entity\Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
     * @return \AppBundle\Entity\Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * Set affiliation
     *
     * @param \AppBundle\Entity\Affiliation $affiliation
     *
     * @return Card
     */
    public function setAffiliation(\AppBundle\Entity\Affiliation $affiliation = null)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * Get affiliation
     *
     * @return \AppBundle\Entity\Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * Set rarity
     *
     * @param \AppBundle\Entity\Rarity $rarity
     *
     * @return Card
     */
    public function setRarity(\AppBundle\Entity\Rarity $rarity = null)
    {
        $this->rarity = $rarity;

        return $this;
    }

    /**
     * Get rarity
     *
     * @return \AppBundle\Entity\Rarity
     */
    public function getRarity()
    {
        return $this->rarity;
    }
    /**
     * @var boolean
     */
    private $hasDie;


    /**
     * Set hasDie
     *
     * @param boolean $hasDie
     *
     * @return Card
     */
    public function setHasDie($hasDie)
    {
        $this->hasDie = $hasDie;

        return $this;
    }

    /**
     * Get hasDie
     *
     * @return boolean
     */
    public function getHasDie()
    {
        return $this->hasDie;
    }
    /**
     * @var string
     */
    private $subtitle;

    /**
     * @var integer
     */
    private $health;

    /**
     * @var string
     */
    private $points;


    /**
     * Set subtitle
     *
     * @param string $subtitle
     *
     * @return Card
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set health
     *
     * @param integer $health
     *
     * @return Card
     */
    public function setHealth($health)
    {
        $this->health = $health;

        return $this;
    }

    /**
     * Get health
     *
     * @return integer
     */
    public function getHealth()
    {
        return $this->health;
    }

    /**
     * Set points
     *
     * @param string $points
     *
     * @return Card
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return string
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Converts cost and points into a comparable value-representation, and then returns the higher value of the both.
     * The higher value of the both is returned.
     * @link https://github.com/fafranco82/swdestinydb/issues/7
     * @return int
     */
    public function getHighestCostPointsValue()
    {
        $cost = $this->getCost();
        $points = $this->getPoints();
        $value = -1;
        if (is_null($cost) && is_null($points)) {
            return $value;
        }

        $cost = is_null($cost) ? 0 : $cost;
        $cost = $cost * 100;

        if (is_null($points)) {
            $points = 0;
        } else {
            $pos = strpos($points, '/');
            if (false === $pos) {
                $points = (int) $points;
                $points = $points * 100;
            } else {
                $oneDiePoints = (int) substr($points, 0, $pos);
                $twoDicePoints = (int) substr($points, $pos + 1);
                $points = $oneDiePoints * 100 + $twoDicePoints;
            }
        }

        return max($cost, $points);
    }
    /**
     * @var boolean
     */
    private $hasErrata;


    /**
     * Set hasErrata
     *
     * @param boolean $hasErrata
     *
     * @return Card
     */
    public function setHasErrata($hasErrata)
    {
        $this->hasErrata = $hasErrata;

        return $this;
    }

    /**
     * Get hasErrata
     *
     * @return boolean
     */
    public function getHasErrata()
    {
        return $this->hasErrata;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reprints;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $reprintOf;


    /**
     * Add reprint
     *
     * @param \AppBundle\Entity\Card $reprint
     *
     * @return Card
     */
    public function addReprint(\AppBundle\Entity\Card $reprint)
    {
        $this->reprints[] = $reprint;

        return $this;
    }

    /**
     * Remove reprint
     *
     * @param \AppBundle\Entity\Card $reprint
     */
    public function removeReprint(\AppBundle\Entity\Card $reprint)
    {
        $this->reprints->removeElement($reprint);
    }

    /**
     * Get reprints
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReprints()
    {
        return $this->reprints;
    }

    /**
     * Set reprintOf
     *
     * @param \AppBundle\Entity\Card $reprintOf
     *
     * @return Card
     */
    public function setReprintOf(\AppBundle\Entity\Card $reprintOf = null)
    {
        $this->reprintOf = $reprintOf;

        return $this;
    }

    /**
     * Get reprintOf
     *
     * @return \AppBundle\Entity\Card
     */
    public function getReprintOf()
    {
        return $this->reprintOf;
    }
	
	/**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $parallelDiceOf;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $parallelDie;


    /**
     * Add reprint
     *
     * @param \AppBundle\Entity\Card $reprint
     *
     * @return Card
     */
    public function addParallelDie(\AppBundle\Entity\Card $parallelDie)
    {
        $this->parallelDiceOf[] = $parallelDie;

        return $this;
    }

    /**
     * Remove reprint
     *
     * @param \AppBundle\Entity\Card $reprint
     */
    public function removeParallelDie(\AppBundle\Entity\Card $parallelDie)
    {
        $this->parallelDiceOf->removeElement($parallelDie);
    }

    /**
     * Get reprints
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParallelDiceOf()
    {
        return $this->parallelDiceOf;
    }

    /**
     * Set parallelDiceOf
     *
     * @param \AppBundle\Entity\Card $parallelDie
     *
     * @return Card
     */
    public function setParallelDie(\AppBundle\Entity\Card $parallelDie = null)
    {
        $this->parallelDie = $parallelDie;

        return $this;
    }

    /**
     * Get parallelDie
     *
     * @return \AppBundle\Entity\Card
     */
    public function getParallelDie()
    {
        return $this->parallelDie;
    }

    /**
     * Set flipCard
     *
     * @param boolean $flipCard
     *
     * @return Card
     */
    public function setFlipCard($flipCard)
    {
        if($flipCard == null)
            $this->flipCard = FALSE;
        else
            $this->flipCard = $flipCard;

        return $this;
    }

    /**
     * Get flipCard
     *
     * @return boolean
     */
    public function getFlipCard()
    {
        return $this->flipCard;
    }
}
