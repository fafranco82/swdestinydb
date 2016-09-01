<?php 

namespace AppBundle\Repository;

class OwnedCardRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\OwnedCard'));
	}

	public function getCollection($userId)
	{
		$qb = $this->createQueryBuilder('o')
			->select('o, c')
			->leftJoin('o.card', 'c')
			->andWhere('o.user = ?1')
			->setParameter(1, $userId)
			->orderBy('c.code', 'ASC');

		return $this->getResult($qb);
	}

	public function getByCardCode($code)
	{
		$qb = $this->createQueryBuilder('o')
			->select('o')
			->leftJoin('o.card', 'c')
			->andWhere('c.code = ?1')
			->setParameter(1, $code);

		return $this->getOneOrNullResult($qb);
	}
}
