<?php 

namespace AppBundle\Repository;

class SetRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Set'));
	}

	public function findAll()
	{
		$qb = $this->createQueryBuilder('s')->orderBy('s.position', 'ASC');
		return $this->getResult($qb);
	}

	public function findByCode($code)
	{
		$qb = $this->createQueryBuilder('s')
			->andWhere('s.code = ?1')
			->setParameter(1, $code);

		return $this->getOneOrNullResult($qb);
	}
}
