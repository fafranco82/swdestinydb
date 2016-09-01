<?php 

namespace AppBundle\Repository;

class FactionRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Faction'));
	}

	public function findAllAndOrderByName()
	{
		$qb = $this->createQueryBuilder('f')->orderBy('f.isPrimary', 'DESC')->addOrderBy('f.name', 'ASC');
		return $this->getResult($qb);
	}
}
