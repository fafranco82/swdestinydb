<?php 

namespace AppBundle\Repository;

class FactionRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Faction'));
	}

	public function findPrimaries()
	{
		$qb = $this->createQueryBuilder('f')->andWhere('f.isPrimary = 1');
		return $this->getResult($qb);
	}
}
