<?php

namespace AppBundle\Entity;

/**
 * Collection
 */
class Collection implements \JsonSerializable
{
    public function jsonSerialize()
    {
        $slots = $this->getSlots();
        
        return $slots->getContent();
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
     * @var \AppBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Collection
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add slot
     *
     * @param \AppBundle\Entity\CollectionSlot $slot
     *
     * @return Collection
     */
    public function addSlot(\AppBundle\Entity\CollectionSlot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param \AppBundle\Entity\CollectionSlot $slot
     */
    public function removeSlot(\AppBundle\Entity\CollectionSlot $slot)
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
}
