<?php 

namespace AppBundle\Entity;

use AppBundle\Model\SlotCollectionProviderInterface;

class Deck extends \AppBundle\Model\ExportableDeck implements \JsonSerializable, SlotCollectionProviderInterface
{
	/**
	 * @return array
	 */
	public function getHistory()
	{
		$slots = $this->getSlots();
		$cards = $slots->getContent();
			
		$snapshots = [];
			
		/**
		 * All changes, with the newest at position 0
		*/
		$changes = $this->getChanges();
			
		/**
		 * Saved changes, with the newest at position 0
		 * @var $savedChanges Deckchange[]
		*/
		$savedChanges =[];
			
		/**
		 * Unsaved changes, with the oldest at position 0
		 * @var $unsavedChanges Deckchange[]
		*/
		$unsavedChanges =[];
			
		foreach($changes as $change) {
			if($change->getIsSaved()) {
				array_push($savedChanges, $change);
			}
			else {
				array_unshift($unsavedChanges, $change);
			}
		}
		$array['unsaved'] = count($unsavedChanges);
			
		// recreating the versions with the variation info, starting from $preversion
		$preversion = $cards;
		foreach ( $savedChanges as $change ) {
			$variation = json_decode ( $change->getVariation(), TRUE );
			$row = [
					'variation' => $variation,
					'is_saved' => $change->getIsSaved(),
					'version' => $change->getVersion(),
					'content' => $preversion,
					'date_creation' => $change->getDateCreation()->format('c'),
			];
			array_unshift ( $snapshots, $row );
				
			// applying variation to create 'next' (older) preversion
			foreach ( $variation[0] as $code => $qtys ) {
                $preversion[$code]['quantity'] = $preversion[$code]['quantity'] - $qtys['quantity'];
				$preversion[$code]['dice'] = $preversion[$code]['dice'] - $qtys['dice'];
				if ($preversion[$code]['quantity'] == 0 && $preversion[$code]['dice'] == 0) unset ( $preversion[$code] );
			}
			foreach ( $variation[1] as $code => $qtys ) {
				if (! isset ( $preversion[$code] )) $preversion[$code] = array(
                    "quantity" => 0,
                    "dice" => 0
                );
                $preversion[$code]['quantity'] = $preversion[$code]['quantity'] + $qtys['quantity'];
				$preversion[$code]['dice'] = $preversion[$code]['dice'] + $qtys['dice'];
			}
			ksort ( $preversion );
		}
			
		// add last know version with empty diff
		$row = [
				'variation' => null,
				'is_saved' => true,
				'version' => "0.0",
				'content' => $preversion,
				'date_creation' => $this->getDateCreation()->format('c')
		];
		array_unshift ( $snapshots, $row );
			
		// recreating the snapshots with the variation info, starting from $postversion
		$postversion = $cards;
		foreach ( $unsavedChanges as $change ) {
			$variation = json_decode ( $change->getVariation(), TRUE );
			$row = [
					'variation' => $variation,
					'is_saved' => $change->getIsSaved(),
					'version' => $change->getVersion(),
					'date_creation' => $change->getDateCreation()->format('c'),
			];
				
			// applying variation to postversion
			foreach ( $variation[0] as $code => $qtys ) {
				if (! isset ( $postversion[$code] )) $postversion[$code] = array(
                    "quantity" => 0,
                    "dice" => 0
                );;
                $postversion[$code]['quantity'] = $postversion[$code]['quantity'] + $qtys['quantity'];
				$postversion[$code]['dice'] = $postversion[$code]['dice'] + $qtys['dice'];
			}
			foreach ( $variation[1] as $code => $qtys ) {
                $postversion[$code]['quantity'] = $postversion[$code]['quantity'] - $qtys['quantity'];
				$postversion[$code]['dice'] = $postversion[$code]['dice'] - $qtys['dice'];
				if ($postversion[$code]['quantity'] == 0 && $postversion[$code]['dice'] == 0) unset ( $postversion[$code] );
			}
			ksort ( $postversion );
				
			// add postversion with variation that lead to it
			$row['content'] = $postversion;
			array_push ( $snapshots, $row );
		}
		
		return $snapshots;
	}
	
	public function jsonSerialize()
	{
		$array = parent::getArrayExport();
		$array['problem'] = $this->getProblem();
		$array['tags'] = $this->getTags();
		
		return $array;
	}
	
	public function getIsUnsaved()
	{
		$changes = $this->getChanges();

		foreach($changes as $change) {
			if(!$change->getIsSaved()) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
     * @var integer
     */
    private $id;

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
     * @var string
     */
    private $descriptionMd;

    /**
     * @var string
     */
    private $problem;

    /**
     * @var string
     */
    private $tags;
    
    /**
     * @var integer
     */
    private $majorVersion;
    
    /**
     * @var integer
     */
    private $minorVersion;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $changes;

    /**
     * @var \AppBundle\Entity\User
     */
    private $user;

    /**
     * @var \AppBundle\Entity\Set
     */
    private $lastSet;

    /**
     * @var \AppBundle\Entity\Decklist
     */
    private $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->changes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->minorVersion = 0;
        $this->majorVersion = 0;
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
     * Set name
     *
     * @param string $name
     *
     * @return Deck
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
     * @return Deck
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
     * @return Deck
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
     * Set descriptionMd
     *
     * @param string $descriptionMd
     *
     * @return Deck
     */
    public function setDescriptionMd($descriptionMd)
    {
        $this->descriptionMd = $descriptionMd;

        return $this;
    }

    /**
     * Get descriptionMd
     *
     * @return string
     */
    public function getDescriptionMd()
    {
        return $this->descriptionMd;
    }

    /**
     * Set problem
     *
     * @param string $problem
     *
     * @return Deck
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;

        return $this;
    }

    /**
     * Get problem
     *
     * @return string
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Set tags
     *
     * @param string $tags
     *
     * @return Deck
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add slot
     *
     * @param \AppBundle\Entity\Deckslot $slot
     *
     * @return Deck
     */
    public function addSlot(\AppBundle\Entity\Deckslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param \AppBundle\Entity\Deckslot $slot
     */
    public function removeSlot(\AppBundle\Entity\Deckslot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Get slots
     *
     * @return \AppBundle\Model\SlotCollectionInterface
     */
    public function getSlots()
    {
        return new \AppBundle\Model\SlotCollectionDecorator($this->slots, $this->format);
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Decklist $child
     *
     * @return Deck
     */
    public function addChild(\AppBundle\Entity\Decklist $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Decklist $child
     */
    public function removeChild(\AppBundle\Entity\Decklist $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add change
     *
     * @param \AppBundle\Entity\Deckchange $change
     *
     * @return Deck
     */
    public function addChange(\AppBundle\Entity\Deckchange $change)
    {
        $this->changes[] = $change;

        return $this;
    }

    /**
     * Remove change
     *
     * @param \AppBundle\Entity\Deckchange $change
     */
    public function removeChange(\AppBundle\Entity\Deckchange $change)
    {
        $this->changes->removeElement($change);
    }

    /**
     * Get changes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Deck
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
     * Set lastSet
     *
     * @param \AppBundle\Entity\Set $lastSet
     *
     * @return Deck
     */
    public function setLastSet(\AppBundle\Entity\Set $lastSet = null)
    {
        $this->lastSet = $lastSet;

        return $this;
    }

    /**
     * Get lastSet
     *
     * @return \AppBundle\Entity\Set
     */
    public function getLastSet()
    {
        return $this->lastSet;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Decklist $parent
     *
     * @return Deck
     */
    public function setParent(\AppBundle\Entity\Decklist $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Decklist
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set majorVersion
     *
     * @param integer $majorVersion
     *
     * @return Deck
     */
    public function setMajorVersion($majorVersion)
    {
        $this->majorVersion = $majorVersion;

        return $this;
    }

    /**
     * Get majorVersion
     *
     * @return integer
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }

    /**
     * Set minorVersion
     *
     * @param integer $minorVersion
     *
     * @return Deck
     */
    public function setMinorVersion($minorVersion)
    {
        $this->minorVersion = $minorVersion;

        return $this;
    }

    /**
     * Get minorVersion
     *
     * @return integer
     */
    public function getMinorVersion()
    {
        return $this->minorVersion;
    }
    
    public function getVersion()
    {
    	return $this->majorVersion . "." . $this->minorVersion;
    }
    /**
     * @var \AppBundle\Entity\Affiliation
     */
    private $affiliation;


    /**
     * Set affiliation
     *
     * @param \AppBundle\Entity\Affiliation $affiliation
     *
     * @return Deck
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
     * @var \AppBundle\Entity\Format
     */
    private $format;


    /**
     * Set format
     *
     * @param \AppBundle\Entity\Format $format
     *
     * @return Deck
     */
    public function setFormat(\AppBundle\Entity\Format $format = null)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return \AppBundle\Entity\Format
     */
    public function getFormat()
    {
        return $this->format;
    }
}
