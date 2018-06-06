<?php 

namespace AppBundle\Repository;

class CardRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Card'));
	}

	public function findAll()
	{
		$qb = $this->createQueryBuilder('c')
			->select('c, t, f, s, a, r')
			->join('c.type', 't')
			->join('c.faction', 'f')
			->join('c.affiliation', 'a')
		    ->join('c.rarity', 'r')
			->join('c.set', 's')
			->orderBY('c.code', 'ASC');

		return $this->getResult($qb);
	}

	public function findByType($type)
	{
		$qb = $this->createQueryBuilder('c')
			->select('c, s')
			->join('c.set', 's')
			->join('c.type', 't')
			->andWhere('t.code = ?1')
			->orderBY('c.code', 'ASC');

		$qb->setParameter(1, $type);

		return $this->getResult($qb);
	}

	public function findByCode($code)
	{
		$qb = $this->createQueryBuilder('c')
			->select('c, t, f, s')
			->join('c.type', 't')
			->join('c.faction', 'f')
			->join('c.set', 's')
			->andWhere('c.code = ?1');

		$qb->setParameter(1, $code);

		return $this->getOneOrNullResult($qb);
	}

	public function findAllByCodes($codes)
	{
		$qb = $this->createQueryBuilder('c')
			->select('c, t, f, s')
			->join('c.type', 't')
			->join('c.faction', 'f')
			->join('c.set', 's')
			->andWhere('c.code in (?1)')
			->orderBY('c.code', 'ASC');

		$qb->setParameter(1, $codes);

		return $this->getResult($qb);
	}

	public function findByRelativePosition($card, $position)
	{
		$qb = $this->createQueryBuilder('c')
			->select('c')
			->join('c.set', 's')
			->andWhere('s.code = ?1')
			->andWhere('c.position = ?2');

		$qb->setParameter(1, $card->getSet()->getCode());
		$qb->setParameter(2, $card->getPosition()+$position);

		return $this->getOneOrNullResult($qb);
	}

	public function findPreviousCard($card)
	{
		return $this->findByRelativePosition($card, -1);
	}

	public function findNextCard($card)
	{
		return $this->findByRelativePosition($card, 1);
	}

	/*
	public function findSubtypes()
	{
		$qb = $this->createQueryBuilder('c')
			->select('DISTINCT c.subtype')
			->andWhere("c.subtype != ''");
		return $this->getResult($qb);
	}
	*/
}
