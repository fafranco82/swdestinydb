<?php

namespace AppBundle\Model;

interface SlotCollectionProviderInterface
{
	/**
	 * @return SlotCollectionInterface
	 */
	public function getSlots();
}