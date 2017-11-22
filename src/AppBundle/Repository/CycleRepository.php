<?php 

namespace AppBundle\Repository;

class CycleRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Cycle'));
	}

	public function findAll()
	{
		$qb = $this->createQueryBuilder('y')->orderBy('y.position', 'ASC');
		return $this->getResult($qb);
	}

	public function findByCode($code)
	{
		$qb = $this->createQueryBuilder('y')
			->andWhere('y.code = ?1')
			->setParameter(1, $code);

		return $this->getOneOrNullResult($qb);
	}
}
