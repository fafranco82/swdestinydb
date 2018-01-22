<?php 

namespace AppBundle\Repository;

class FormatRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Format'));
	}

	public function findAll()
	{
		$qb = $this->createQueryBuilder('f');
		return $this->getResult($qb);
	}

	public function findByCode($code)
	{
		$qb = $this->createQueryBuilder('f')
			->andWhere('f.code = ?1')
			->setParameter(1, $code);

		return $this->getOneOrNullResult($qb);
	}
}
