<?php

namespace AppBundle\Entity;

/**
 * StarterPack
 */
class StarterPack implements \JsonSerializable, \Serializable
{
    public function jsonSerialize()
    {
        return array(
            "code" => $this->code,
            "name" => $this->name,
            "set_name" => $this->set->getName(),
            "slots" => $this->getSlots()->getContent()
        );
    }

    public function serialize()
    {
        $serialized = array(
            "code" => $this->code,
            "name" => $this->name,
            "slots" => $this->getSlots()->getContent()
        );

        if($this->getSet())
            $serialized['set_code'] = $this->getSet()->getCode();

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * @var \AppBundle\Entity\Set
     */
    private $set;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return StarterPack
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
     * @return StarterPack
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return StarterPack
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
     * @return StarterPack
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
     * Add slot
     *
     * @param \AppBundle\Entity\StarterPackSlot $slot
     *
     * @return StarterPack
     */
    public function addSlot(\AppBundle\Entity\StarterPackSlot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param \AppBundle\Entity\StarterPackSlot $slot
     */
    public function removeSlot(\AppBundle\Entity\StarterPackSlot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Get slots
     *
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function getSlots()
    {
        return new \AppBundle\Model\SlotCollectionDecorator($this->slots);
    }

    /**
     * Set set
     *
     * @param \AppBundle\Entity\Set $set
     *
     * @return StarterPack
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
}
