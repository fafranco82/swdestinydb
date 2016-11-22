<?php


namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use AppBundle\Model\SlotCollectionInterface;
use AppBundle\Model\SlotInterface;
use AppBundle\Model\SlotCollectionDecorator;
use AppBundle\Entity\Deckslot;
use Doctrine\Common\Collections\ArrayCollection;
use Functional as F;

/**
 * 
 * @author AWOPM
 * @property $em EntityManager
 */
class Diff
{
    public function __construct(EntityManager $doctrine)
    {
        $this->em = $doctrine;
    }
    
    /**
     * Computes the diff between a list of SlotCollectionInterface
     * Mutates its arguments by removing the intersection from them
     * @param SlotCollectionInterface[] $list_slots
     * @return SlotCollectionInterface $intersection
     */
    public function getSlotsDiff($list_slots)
    {
    	// list of all the codes found in every slots
    	$cardCodes = [];
    	
    	/* @var $slots SlotCollectionInterface */
    	foreach($list_slots as $slots)
    	{
    		/* @var $slot SlotInterface */
    		foreach($slots as $slot)
    		{
    			// since we're going to mutate the slots, we detach them first
    			$this->em->detach($slot);
    			
    			$cardCodes[] = $slot->getCard()->getCode();
    		}
    	}
    	
    	// then we count each code occurence
    	$cardCodeCounts = array_count_values($cardCodes);
    	 
    	// list of the slots common to every slots, after removing them from every slots
    	$intersection = new ArrayCollection();
    	 
    	foreach($cardCodeCounts as $cardCode => $occurences)
    	{
    		// if this card cannot be found in every slots, move on
    		if($occurences < count($list_slots)) continue;
    		
    		// we'll get the card later
    		$card = null;
    		
    		// this is the list of where we can find that code in each flatList
    		$indexes = [];
    		
    		// this is the list of the quantities we found in each flatList
    		$quantities = [];

            // this is the list of the dice we found in each flatList
            $dice = [];
    		
    		// searching all slots for that code
    		foreach($list_slots as $j => $slots)
    		{
    			// searching the slots
    			foreach($slots as $k => $slot) {
    				if($slot->getCard()->getCode() === $cardCode) {
    					$card = $slot->getCard();
    					$indexes[$j] = $k;
    					$quantities[$j] = $slot->getQuantity();
                        $dice[$j] = $slot->getDice();
    					break;
    				}
    			}
    		}
    
    		// we need to find the minimum quantity among all SlotCollections
    		$minimum = min($quantities);
            $minimumDice = min($dice);
    		 
    		// we create a slot for this
    		$slot = new Deckslot();
    		$slot->setCard($card);
    		$slot->setQuantity($minimum);
            $slot->setDice($minimumDice);
    		 
    		// we add this slot to the list of common slots
    		$intersection->add($slot);
    		
    		// then we remove that many cards from every SlotCollection
    		foreach($indexes as $j => $index)
    		{
    			$slot = $list_slots[$j][$index];
    			$slot->setQuantity($slot->getQuantity() - $minimum);
                $slot->setDice($slot->getDice() - $minimumDice);

                // if the card is a unique character
                if($card->getType()->getCode()==='character' && $card->getIsUnique()) {
                    // and there is more dice
                    if($slot->getDice() > 0) {
                        $slot->setQuantity(1); //set again cards qty to 1
                    }
                }
    		}
    	}
    	
    	return new SlotCollectionDecorator($intersection);
    }

    public function t_diffContents($decks)
    {
        $intersect = array();
        $restDecks = $decks;
        $firstDeck = array_shift($restDecks);
        
        foreach($firstDeck as $code => $qtys)
        {
            if(F\every($restDecks, function($deck) use ($code) { return array_key_exists($code, $deck); })) {
                $intersect[$code] = array(
                    "quantity" => F\minimum(F\map($decks, function($deck) use($code) { return $deck[$code]["quantity"]; })),
                    "dice" => F\minimum(F\map($decks, function($deck) use($code) { return $deck[$code]["dice"]; }))
                );

                forEach($decks as &$deck)
                {
                    $deck[$code] = array(
                        "quantity" => $deck[$code]["quantity"] - $intersect[$code]["quantity"],
                        "dice" => $deck[$code]["dice"] - $intersect[$code]["dice"]
                    );
                }
            }
        }

        forEach($decks as &$deck)
        {
            forEach($deck as $code => $qtys)
            {
                if($qtys["quantity"]==0 && $qtys["dice"]==0)
                    unset($deck[$code]);
            }
            ksort($deck);
        }
        forEach($intersect as $code => $qtys)
        {
            if($qtys["quantity"]==0 && $qtys["dice"]==0)
                unset($intersect[$code]);
        }
        ksort($intersect);

        return array($decks, $intersect);
    }
    
    public function diffContentsProp($decks, $prop)
    {

        // n flat lists of the cards of each decklist
        $ensembles = [];
        foreach($decks as $deck) {
            $cards = [];
            foreach($deck as $code => $qtys) {
                $qty = $qtys[$prop];
                for($i=0; $i<$qty; $i++) $cards[] = $code;
            }
            $ensembles[] = $cards;
        }
        
        // 1 flat list of the cards seen in every decklist
        $conjunction = [];
        for($i=0; $i<count($ensembles[0]); $i++) {
            $code = $ensembles[0][$i];
            $indexes = array($i);
            for($j=1; $j<count($ensembles); $j++) {
                $index = array_search($code, $ensembles[$j]);
                if($index !== FALSE) $indexes[] = $index;
                else break;
            }
            if(count($indexes) === count($ensembles)) {
                $conjunction[] = $code;
                for($j=0; $j<count($indexes); $j++) {
                    $list = $ensembles[$j];
                    array_splice($list, $indexes[$j], 1);
                    $ensembles[$j] = $list;
                }
                $i--;
            }
        }
        
        $listings = [];
        for($i=0; $i<count($ensembles); $i++) {
            $listings[$i] = array_count_values($ensembles[$i]);
        }
        $intersect = array_count_values($conjunction);
        
        return array($listings, $intersect);
    }

    public function diffContents($decks)
    {
        return $this->t_diffContents($decks);
    }
}