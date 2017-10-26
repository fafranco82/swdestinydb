<?php 

namespace AppBundle\Repository;

use Doctrine\DBAL\Connection;

class DeckRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Deck'));
	}

	public function find($id)
	{
		$qb = $this->createQueryBuilder('d')
			->select('d, a, ds, c, s, t')
			->join('d.affiliation', 'a')
			->leftJoin('d.slots', 'ds')
			->leftJoin('ds.card', 'c')
			->leftJoin('c.set', 's')
			->leftJoin('c.type', 't')
			->andWhere('d.id = ?1');

		$qb->setParameter(1, $id);
		return $this->getOneOrNullResult($qb);
	}

	public function findWithCard($cards)
	{
		if(!is_array($cards)) $cards = [$cards];
		$qb = $this->createQueryBuilder('d')
			->select('d')
			->leftJoin('d.slots', 'ds')
			->leftJoin('ds.card', 'c')
			->andWhere('c.code IN (:codes)')
			->setParameter('codes', $cards, Connection::PARAM_STR_ARRAY);
		return $this->getResult($qb);
	}
}
