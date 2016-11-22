<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WidgetsController extends Controller
{
	public function decklistOverviewAction($decklist_id)
	{
		$typeNames = [];
        foreach($this->getDoctrine()->getRepository('AppBundle:Type')->findAll() as $type) {
        	$typeNames[$type->getCode()] = $type->getName();
        }

        $factionNames = [];
        foreach($this->getDoctrine()->getRepository('AppBundle:Faction')->findAll() as $faction) {
            $factionNames[$faction->getCode()] = $faction->getName();
        }

		$decklistRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Decklist');
        $decklist = $decklistRepo->find($decklist_id);
        $array = [];
	    $array['decklist'] = $decklist;

        $characterDeck = $decklist->getSlots()->getCharacterRow();
        $array['character_deck'] = $characterDeck;

        $array['count_by_type'] = $decklist->getSlots()->getCountByType();

        $decklist_factions = $decklist->getSlots()->getCountByFaction();
        arsort($decklist_factions);
        $array['factions'] = array_keys(array_filter($decklist_factions, function($v) {
            return $v > 0;
        }));

		return $this->render('AppBundle:Widgets:layout.js.twig', array(
			'content' => $this->renderView('AppBundle:Widgets:decklist_overview.html.twig', array(
				'data' => $array,
				'typeNames' => $typeNames,
            	'factionNames' => $factionNames
			))
		));
	}
}
