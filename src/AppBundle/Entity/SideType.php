<?php 

namespace AppBundle\Entity;

class SideType implements \Gedmo\Translatable\Translatable, \Serializable
{
	public function serialize() {
		return [
				'code' => $this->code,
                'icon' => $this->icon,
				'name' => $this->name
		];
	}
	
	public function unserialize($serialized) {
		throw new \Exception("unserialize() method unsupported");
	}

    public function toString() {
        return $this->name;
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sides;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Set code
     *
     * @param string $code
     *
     * @return Type
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
     * @return Type
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
     * Add side
     *
     * @param \AppBundle\Entity\Side $side
     *
     * @return Type
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

    /*
    * I18N vars
    */
    private $locale = 'en';

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    /**
     * @var string
     */
    private $icon;


    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return SideType
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
