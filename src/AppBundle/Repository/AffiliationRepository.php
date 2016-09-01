<?php 

namespace AppBundle\Repository;

class AffiliationRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Affiliation'));
	}

	public function findPrimaries()
	{
		$qb = $this->createQueryBuilder('a')
			->select('a')
			->andWhere('a.isPrimary = 1')
			->orderBy('a.name', 'ASC');
		return $this->getResult($qb);
	}

	public function findByCode($code)
	{
		$qb = $this->createQueryBuilder('a')
			->select('a')
			->andWhere('a.code = ?1')
			->setParameter(1, $code);
		return $this->getOneOrNullResult($qb);
	}

	public function findAllAndOrderByName()
	{
		$qb = $this->createQueryBuilder('a')
			->orderBY('a.name', 'ASC');

		return $this->getResult($qb);
	}
}
