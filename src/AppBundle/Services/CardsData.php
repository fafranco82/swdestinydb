<?php


namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function Functional\map;

/*
 *
 */
class CardsData
{
	public function __construct(Registry $doctrine, RequestStack $request_stack, Router $router, AssetsHelper $assets_helper, TranslatorInterface $translator,ContainerInterface $container, $rootDir) {
		$this->doctrine = $doctrine;
        $this->request_stack = $request_stack;
        $this->router = $router;
        $this->assets_helper = $assets_helper;
        $this->translator = $translator;
        $this->container = $container;
        $this->rootDir = $rootDir;
	}

	/**
	 * Searches for and replaces symbol tokens with markup in a given text.
	 * @param string $text
	 * @return string
	 */
	public function replaceSymbols($text)
	{
		static $displayTextReplacements = [
			'[blank]' => '<span class="icon-blank"></span>',
			'[discard]' => '<span class="icon-discard"></span>',
			'[disrupt]' => '<span class="icon-disrupt"></span>',
			'[focus]' => '<span class="icon-focus"></span>',
			'[melee]' => '<span class="icon-melee"></span>',
			'[ranged]' => '<span class="icon-ranged"></span>',
			'[indirect]' => '<span class="icon-indirect"></span>',
			'[shield]' => '<span class="icon-shield"></span>',
			'[resource]' => '<span class="icon-resource"></span>',
			'[special]' => '<span class="icon-special"></span>',
			'[unique]' => '<span class="icon-unique"></span>',
			'[AW]' => '<span class="icon-set-AW"></span>',
			'[SoR]' => '<span class="icon-set-SoR"></span>',
			'[EaW]' => '<span class="icon-set-EaW"></span>',
			'[TPG]' => '<span class="icon-set-TPG"></span>',
			'[LEG]' => '<span class="icon-set-LEG"></span>'
		];
		
		return str_replace(array_keys($displayTextReplacements), array_values($displayTextReplacements), $text);
	}

	/**
	 * Searches for single keywords and surround them with <abbr>
	 * @param string $text
	 * @return string
	 */
	public function addAbbrTags($text)
	{
		$locale = $this->request_stack->getCurrentRequest()->getLocale();

		foreach(\AppBundle\Helper\Constants::KEYWORDS as $keyword)
		{
			/** @Ignore */
			$translated = $this->translator->trans('keyword.'.$keyword.".name", array(), "messages", $locale);
			
			$text = preg_replace_callback("/\b($translated)\b/i", function ($matches) use ($keyword) {
				return "<abbr data-keyword=\"$keyword\">".$matches[1]."</abbr>";
			}, $text);
		}
		
		
		
		return $text;
	}
	
	public function splitInParagraphs($text)
	{
		if(empty($text)) return '';
		return implode(array_map(function ($l) { return "<p>$l</p>"; }, preg_split('/[\r\n]+/', $text)));	
	}

	public function allsetsdata()
	{
		$list_sets = $this->doctrine->getRepository('AppBundle:Set')->findAll();
		$lines = [];
		/* @var $cycle \AppBundle\Entity\Cycle */
		foreach($list_sets as $set) {
			$known = count($set->getCards());
			$max = $set->getSize();

			$label = $set->getPosition() . '. <span class="icon-set-'.$set->getCode().'"></span> ' . $set->getName();
			if($known < $max) {
				$label = sprintf("%s (%d/%d)", $label, $known, $max);
			}

			$lines[] = array(
					"code" => $set->getCode(),
					"label" => $label,
					"available" => $set->getDateRelease() ? true : false,
					"url" => $this->router->generate('cards_list', array('set_code' => $set->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
			);
		}
		return $lines;
	}

	public function allsetsdatathreaded()
	{
		$list_sets = $this->doctrine->getRepository('AppBundle:Set')->findAll();
		$sets = [];
		
		/* @var $set \AppBundle\Entity\Cycle */
		foreach($list_sets as $set) {
			$known = count($set->getCards());
			$max = $set->getSize();
		
			$label = $set->getName();
				
			if($known < $max) {
				$label = sprintf("%s (%d/%d)", $label,$known, $max);
			}
		
			$sets[] = [
					"code" => $set->getCode(),
					"label" => $label,
					"available" => $set->getDateRelease() ? true : false,
					"url" => $this->router->generate('cards_list', array('set_code' => $set->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
			];
		}
			
		return $sets;
	}
	
	public function getPrimaryFactions()
	{
		$factions = $this->doctrine->getRepository('AppBundle:Faction')->findPrimaries();
		return $factions;
	}

	public function get_search_rows($conditions, $sortorder, $forceempty = false)
	{
		$i=0;

		// construction de la requete sql
		$repo = $this->doctrine->getRepository('AppBundle:Card');
		$qb = $repo->createQueryBuilder('c')
		           ->select('c', 's', 't', 'f', 'a', 'y', 'd', 'b')
				   ->leftJoin('c.set', 's')
				   ->leftJoin('c.type', 't')
				   ->leftJoin('c.faction', 'f')
				   ->leftJoin('c.affiliation', 'a')
				   ->leftJoin('c.rarity', 'y')
				   ->leftJoin('c.subtypes', 'b')
				   ->leftJoin('c.sides', 'd')
				   ;
		$qb2 = null;
		$qb3 = null;

		foreach($conditions as $condition)
		{
			$searchCode = array_shift($condition);
			$searchName = \AppBundle\Controller\SearchController::$searchKeys[$searchCode];
			$searchType = \AppBundle\Controller\SearchController::$searchTypes[$searchCode];
			$operator = array_shift($condition);

			switch($searchType)
			{
				case 'boolean':
				{
					switch($searchCode)
					{
						default:
						{
							if(($operator == ':' && $condition[0]) || ($operator == '!' && !$condition[0])) {
								$qb->andWhere("(c.$searchName = 1)");
							} else {
								$qb->andWhere("(c.$searchName = 0)");
							}
							$i++;
							break;
						}
					}
					break;
				}
				case 'integer':
				{
					switch($searchCode)
					{
						default:
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "(c.$searchName = ?$i)"; break;
									case '!': $or[] = "(c.$searchName != ?$i)"; break;
									case '<': $or[] = "(c.$searchName < ?$i)"; break;
									case '>': $or[] = "(c.$searchName > ?$i)"; break;
								}
								$qb->setParameter($i++, $arg);
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
					}
					break;
				}
				case 'code':
				{
					switch($searchCode)
					{
						case 's':
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "(s.code = ?$i)"; break;
									case '!': $or[] = "(s.code != ?$i)"; break;
									case '<':
										if(!isset($qb2)) {
											$qb2 = $this->doctrine->getRepository('AppBundle:Set')->createQueryBuilder('s2');
											$or[] = $qb->expr()->lt('s.dateRelease', '(' . $qb2->select('s2.dateRelease')->where("s2.code = ?$i")->getDql() . ')');
										}
										break;
									case '>':
										if(!isset($qb3)) {
											$qb3 = $this->doctrine->getRepository('AppBundle:Set')->createQueryBuilder('s3');
											$or[] = $qb->expr()->gt('s.dateRelease', '(' . $qb3->select('s3.dateRelease')->where("s3.code = ?$i")->getDql() . ')');
										}
										break;
								}
								$qb->setParameter($i++, $arg);
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
						case 'b':
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "(b.code = ?$i)"; break;
									case '!': $or[] = "(b.code  != ?$i)"; break;
								}
								$qb->setParameter($i++, $arg);
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
						default: // type and faction
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "($searchCode.code = ?$i)"; break;
									case '!': $or[] = "($searchCode.code != ?$i)"; break;
								}
								$qb->setParameter($i++, $arg);
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
					}
					break;
				}
				case 'string': {
					switch($searchCode)
					{
						case '': // name or index
						{
							$or = [];
							foreach($condition as $arg) {
								$code = preg_match('/^\d\d\d\d\d$/u', $arg);
								$acronym = preg_match('/^[A-Z]{2,}$/', $arg);
								if($code) {
									$or[] = "(c.code = ?$i)";
									$qb->setParameter($i++, $arg);
								} else if($acronym) {
									$or[] = "(BINARY(c.name) like ?$i)";
									$qb->setParameter($i++, "%$arg%");
									$like = implode('% ', str_split($arg));
									$or[] = "(REPLACE(c.name, '-', ' ') like ?$i)";
									$qb->setParameter($i++, "$like%");
								} else {
									$or[] = "(c.name like ?$i)";
									$qb->setParameter($i++, "%$arg%");
								}
							}
							$qb->andWhere(implode(" or ", $or));
							break;
						}
						case 'x': // text
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "(c.text like ?$i)"; break;
									case '!': $or[] = "(c.text not like ?$i)"; break;
								}
								$qb->setParameter($i++, "%$arg%");
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
						case 'a': // flavor
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "(c.flavor like ?$i)"; break;
									case '!': $or[] = "(c.flavor not like ?$i)"; break;
								}
								$qb->setParameter($i++, "%$arg%");
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
						case 'i': // illustrator
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case ':': $or[] = "(c.illustrator = ?$i)"; break;
									case '!': $or[] = "(c.illustrator != ?$i)"; break;
								}
								$qb->setParameter($i++, $arg);
							}
							$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
							break;
						}
						case 'r': // release
						{
							$or = [];
							foreach($condition as $arg) {
								switch($operator) {
									case '<': $or[] = "(p.dateRelease <= ?$i)"; break;
									case '>': $or[] = "(p.dateRelease > ?$i or p.dateRelease IS NULL)"; break;
								}
								if($arg == "now") $qb->setParameter($i++, new \DateTime());
								else $qb->setParameter($i++, new \DateTime($arg));
							}
							$qb->andWhere(implode(" or ", $or));
							break;
						}
					}
					break;
				}
			}
		}

		if(!$i && !$forceempty) {
			return;
		}
		switch($sortorder) {
			case 'set': $qb->orderBy('s.position')->addOrderBy('c.position'); break;
			case 'faction': $qb->orderBy('c.faction')->addOrderBy('c.type'); break;
			case 'type': $qb->orderBy('c.type')->addOrderBy('c.faction'); break;
			case 'cost': $qb->orderBy('c.type')->addOrderBy('c.cost'); break;
		}
		$qb->addOrderBy('c.name');
		$qb->addOrderBy('c.code');
		$rows = $repo->getResult($qb);

		return $rows;
	}

	/**
	 *
	 * @param \AppBundle\Entity\Card $card
	 * @param string $api
	 * @return multitype:multitype: string number mixed NULL unknown
	 */
	public function getCardInfo($card, $api = false)
	{
		$locale = $this->request_stack->getCurrentRequest()->getLocale();
		$cardinfo = [];

	    $metadata = $this->doctrine->getManager()->getClassMetadata('AppBundle:Card');
	    $fieldNames = $metadata->getFieldNames();
	    $associationMappings = $metadata->getAssociationMappings();

	    foreach($associationMappings as $fieldName => $associationMapping)
	    {
	    	if($associationMapping['isOwningSide']) {
		    	$getter = str_replace(' ', '', ucwords(str_replace('_', ' ', "get_$fieldName")));
	    		if(array_key_exists('joinTable', $associationMapping))
	    		{
	    			$associationEntities = $card->$getter();
	    			if(count($associationEntities) == 0) continue; 

	    			$cardinfo[$fieldName] = [];
	    			foreach($associationEntities->getValues() as $associationEntity)
	    			{
	    				$cardinfo[$fieldName][] = [
	    					"code" => $associationEntity->getCode(),
	    					"name" => $associationEntity->getName()
	    				];
	    			}
	    		}
	    		else
	    		{
		    		$associationEntity = $card->$getter();
		    		if(!$associationEntity) continue;

	    			$cardinfo[$fieldName.'_code'] = $associationEntity->getCode();
	    			$cardinfo[$fieldName.'_name'] = $associationEntity->getName();
	    		}
	    	}

	    	if($fieldName=='sides')
	    	{
	    		$cardinfo['sides'] = $card->getSides();
	    	}
	    }

	    foreach($fieldNames as $fieldName)
	    {
	    	$getter = str_replace(' ', '', ucwords(str_replace('_', ' ', "get_$fieldName")));
	    	$value = $card->$getter();
			switch($metadata->getTypeOfField($fieldName)) {
				case 'datetime':
				case 'date':
					continue 2;
					break;
				case 'boolean':
					$value = (boolean) $value;
					break;
			}
			$fieldName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $fieldName)), '_');
	    	$cardinfo[$fieldName] = $value;
	    }

	    if(!$card->getReprints()->isEmpty())
	    {
		    $cardinfo['reprints'] = [];
		    foreach ($card->getReprints() as $reprint) {
		    	$cardinfo['reprints'][] = $reprint->getCode();
		    }
		}

	    if($card->getReprintOf() != NULL)
	    {
	    	$cardinfo['reprint_of'] = $card->getReprintOf()->getCode();
	    }


		$cardinfo['url'] = $this->router->generate('cards_zoom', array('card_code' => $card->getCode()), UrlGeneratorInterface::ABSOLUTE_URL);

		$setcode = str_pad($card->getSet()->getPosition(), 2, '0', STR_PAD_LEFT);
		$imageurl = $this->assets_helper->getUrl("/bundles/cards/{$locale}/{$setcode}/{$card->getCode()}.jpg");
        $imagepath = $this->rootDir . '/../web' . preg_replace('/\?.*/', '', $imageurl);
        if(file_exists($imagepath)) {
            $cardinfo['imagesrc'] = $this->ensureUrlIsAbsolute($imageurl);
            if($locale != 'en') {
	        	$cardinfo['imagesrc_en'] = $this->ensureUrlIsAbsolute($this->assets_helper->getUrl("/bundles/cards/en/{$setcode}/{$card->getCode()}.jpg"));
	        }
        } else {
            $cardinfo['imagesrc'] = null;
        }

		// look for another card with the same name to set the label
		/*$homonyms = $this->doctrine->getRepository('AppBundle:Card')->findBy(['name' => $card->getName()]);
		if(count($homonyms) > 1) {
			$cardinfo['label'] = $card->getName() . ' (' . $card->getSet()->getCode() . ')';
		} else {
			$cardinfo['label'] = $card->getName();
		}*/

		$cardinfo['label'] = $card->getName();
		if($card->getSubtitle())
			$cardinfo['label'] .= ' - ' . $card->getSubtitle();

		if($api) {
			unset($cardinfo['id']);
            $cardinfo['cp'] = $card->getHighestCostPointsValue();
			if(!$cardinfo['has_die']) unset($cardinfo['sides']);
			else $cardinfo['sides'] = map($cardinfo['sides'], function($side) { return $side->toString(); });

		} else {
			$cardinfo['text'] = $this->replaceSymbols($cardinfo['text']);
			$cardinfo['text'] = $this->addAbbrTags($cardinfo['text']);
			$cardinfo['text'] = $this->splitInParagraphs($cardinfo['text']);
			
			$cardinfo['flavor'] = $this->replaceSymbols($cardinfo['flavor']);
		}

		return $cardinfo;
	}

	public function get_card_by_code($code)
	{
		$card = $this->doctrine->getRepository('AppBundle:Card')->findByCode($code);
		if($card) {
			return $this->getCardInfo($card);
		}
	}

	/**
     * Ensures an URL is absolute, if possible.
     *
     * @param string $url The URL that has to be absolute
     *
     * @return string The absolute URL
     *
     * @throws \RuntimeException
     */
    private function ensureUrlIsAbsolute($url)
    {
        if (false !== strpos($url, '://') || 0 === strpos($url, '//')) {
            return $url;
        }

        $request = $this->request_stack->getCurrentRequest();
        if (!$request) {
            throw new \RuntimeException('To generate an absolute URL for an asset, the Symfony Routing component is required.');
        }

        if ('' === $host = $request->getHost()) {
            return $url;
        }

        $scheme = $request->getScheme();
        $port = '';

        if ('http' === $scheme && 80 != $request->getPort()) {
            $port = ':'.$request->getPort();
        } elseif ('https' === $scheme && 443 != $request->getPort()) {
            $port = ':'.$request->getPort();
        }

        return $scheme.'://'.$host.$port.$url;
    }

	public function syntax($query)
	{
		// renvoie une liste de conditions (array)
		// chaque condition est un tableau à n>1 éléments
		// le premier est le type de condition (0 ou 1 caractère)
		// les suivants sont les arguments, en OR

		$query = preg_replace('/\s+/u', ' ', trim($query));

		$list = [];
		$cond = null;
		// l'automate a 3 états :
		// 1:recherche de type
		// 2:recherche d'argument principal
		// 3:recherche d'argument supplémentaire
		// 4:erreur de parsing, on recherche la prochaine condition
		// s'il tombe sur un argument alors qu'il est en recherche de type, alors le type est vide
		$etat = 1;
		while($query != "") {
			if($etat == 1) {
				if(isset($cond) && $etat != 4 && count($cond)>2) {
					$list[] = $cond;
				}
				// on commence par rechercher un type de condition
				$match = [];
				if(preg_match('/^(\p{L})([:<>!])(.*)/u', $query, $match)) { // jeton "condition:"
					$cond = array(mb_strtolower($match[1]), $match[2]);
					$query = $match[3];
				} else {
					$cond = array("", ":");
				}
				$etat=2;
			} else {
				if( preg_match('/^"([^"]*)"(.*)/u', $query, $match) // jeton "texte libre entre guillements"
				 || preg_match('/^([\p{L}\p{N}\-\&]+)(.*)/u', $query, $match) // jeton "texte autorisé sans guillements"
				) {
					if(($etat == 2 && count($cond)==2) || $etat == 3) {
						$cond[] = $match[1];
						$query = $match[2];
						$etat = 2;
					} else {
						// erreur
						$query = $match[2];
						$etat = 4;
					}
				} else if( preg_match('/^\|(.*)/u', $query, $match) ) { // jeton "|"
					if(($cond[1] == ':' || $cond[1] == '!') && (($etat == 2 && count($cond)>2) || $etat == 3)) {
						$query = $match[1];
						$etat = 3;
					} else {
						// erreur
						$query = $match[1];
						$etat = 4;
					}
				} else if( preg_match('/^ (.*)/u', $query, $match) ) { // jeton " "
					$query = $match[1];
					$etat=1;
				} else {
					// erreur
					$query = substr($query, 1);
					$etat = 4;
				}
			}
		}
		if(isset($cond) && $etat != 4 && count($cond)>2) {
			$list[] = $cond;
		}
		return $list;
	}

	public function validateConditions($conditions)
	{
		// suppression des conditions invalides
		$numeric = array('<', '>');

		foreach($conditions as $i => $l)
		{
			$searchCode = $l[0];
			$searchOp = $l[1];

			if(in_array($searchOp, $numeric) && \AppBundle\Controller\SearchController::$searchTypes[$searchCode] !== 'integer')
			{
				// operator is numeric but searched property is not
				unset($conditions[$i]);
			}
		}
		
		return array_values($conditions);
	}

	public function buildQueryFromConditions($conditions)
	{
		// reconstruction de la bonne chaine de recherche pour affichage
		return implode(" ", array_map(
				function ($l) {
					return ($l[0] ? $l[0].$l[1] : "")
					. implode("|", array_map(
							function ($s) {
								return preg_match("/^[\p{L}\p{N}\-\&]+$/u", $s) ?$s : "\"$s\"";
							},
							array_slice($l, 2)
					));
				},
				$conditions
		));
	}

    public function get_reviews($card)
    {
        $reviews = $this->doctrine->getRepository('AppBundle:Review')->findBy(array('card' => $card), array('nbVotes' => 'DESC'));

        $response = $reviews;

        return $response;
    }
    
    public function getDistinctTraits()
    {
    	/**
    	 * @var $em \Doctrine\ORM\EntityManager
    	 */
    	$em = $this->doctrine->getManager();
    	$qb = $em->createQueryBuilder();
    	$qb->from('AppBundle:Card', 'c');
    	$qb->select('c.traits');
    	$qb->distinct();
    	$result = $qb->getQuery()->getResult();
    	
    	$traits = [];
    	foreach($result as $card) {
    		$subs = explode('.', $card["traits"]);
    		foreach($subs as $sub) {
    			$traits[trim($sub)] = 1;
    		}
    	}
    	 
    }
}
