<?php 

namespace AppBundle\Repository;

class DeckRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Deck'));
	}

	public function find($id)
	{
		$qb = $this->createQueryBuilder('d')
			->select('d, a, ds, c')
			->join('d.affiliation', 'a')
			->leftJoin('d.slots', 'ds')
			->leftJoin('ds.card', 'c')
			->andWhere('d.id = ?1');

		$qb->setParameter(1, $id);
		return $this->getOneOrNullResult($qb);
	}
}
