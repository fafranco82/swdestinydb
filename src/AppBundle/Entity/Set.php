<?php 

namespace AppBundle\Entity;

class Set implements \Gedmo\Translatable\Translatable, \Serializable
{
    public function serialize() {
        return [
                'code' => $this->code,
                'cycle' => $this->cycle != NULL ? $this->cycle->getCode() : null,
                'date_release' => $this->dateRelease ? $this->dateRelease->format('Y-m-d') : null,
                'name' => $this->name,
                'position' => $this->position,
                'cgdb_id_start' => $this->cgdbIdStart,
                'cgdb_id_end' => $this->cgdbIdEnd,
                'size' => $this->size
        ];
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
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $cgdbIdStart;

    /**
     * @var integer
     */
    private $cgdbIdEnd;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var \DateTime
     */
    private $dateRelease;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cards;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $starterPacks;

    /**
     * @var \AppBundle\Entity\Cycle
     */
    private $cycle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->starterPacks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return Set
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
     * Set name
     *
     * @param string $name
     *
     * @return Set
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
     * Set position
     *
     * @param integer $position
     *
     * @return Set
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
     * Set size
     *
     * @param integer $size
     *
     * @return Set
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set cgdbIdStart
     *
     * @param integer $cgdbIdStart
     *
     * @return Set
     */
    public function setCgdbIdStart($cgdbIdStart)
    {
        $this->cgdbIdStart = $cgdbIdStart;

        return $this;
    }

    /**
     * Get cgdbIdStart
     *
     * @return integer
     */
    public function getCgdbIdStart()
    {
        return $this->cgdbIdStart;
    }

    /**
     * Set cgdbIdEnd
     *
     * @param integer $cgdbIdEnd
     *
     * @return Set
     */
    public function setCgdbIdEnd($cgdbIdEnd)
    {
        $this->cgdbIdEnd = $cgdbIdEnd;

        return $this;
    }

    /**
     * Get cgdbIdEnd
     *
     * @return integer
     */
    public function getCgdbIdEnd()
    {
        return $this->cgdbIdEnd;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Set
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
     * @return Set
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
     * Set dateRelease
     *
     * @param \DateTime $dateRelease
     *
     * @return Set
     */
    public function setDateRelease($dateRelease)
    {
        $this->dateRelease = $dateRelease;

        return $this;
    }

    /**
     * Get dateRelease
     *
     * @return \DateTime
     */
    public function getDateRelease()
    {
        return $this->dateRelease;
    }

    /**
     * Add card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return Set
     */
    public function addCard(\AppBundle\Entity\Card $card)
    {
        $this->cards[] = $card;

        return $this;
    }

    /**
     * Remove card
     *
     * @param \AppBundle\Entity\Card $card
     */
    public function removeCard(\AppBundle\Entity\Card $card)
    {
        $this->cards->removeElement($card);
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Add starterPack
     *
     * @param \AppBundle\Entity\StarterPack $starterPack
     *
     * @return Set
     */
    public function addStarterPack(\AppBundle\Entity\StarterPack $starterPack)
    {
        $this->starterPacks[] = $starterPack;

        return $this;
    }

    /**
     * Remove starterPack
     *
     * @param \AppBundle\Entity\StarterPack $starterPack
     */
    public function removeStarterPack(\AppBundle\Entity\StarterPack $starterPack)
    {
        $this->starterPacks->removeElement($starterPack);
    }

    /**
     * Get starterPacks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStarterPacks()
    {
        return $this->starterPacks;
    }

    /**
     * Set cycle
     *
     * @param \AppBundle\Entity\Cycle $cycle
     *
     * @return Set
     */
    public function setCycle(\AppBundle\Entity\Cycle $cycle = null)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return \AppBundle\Entity\Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }
}
