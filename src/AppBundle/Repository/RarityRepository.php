<?php 

namespace AppBundle\Repository;

class RarityRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Rarity'));
	}

	public function findAll()
	{
		$qb = $this->createQueryBuilder('r')
			->orderBY('r.id', 'ASC');

		return $this->getResult($qb);
	}
}
