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
        $decklist_factions = $decklist->getSlots()->getCountByFaction();
        arsort($decklist_factions);
        $decklist_factions = array_keys(array_filter($decklist_factions, function($v) {
            return $v > 0;
        }));

		return $this->render('AppBundle:Widgets:layout.js.twig', array(
            'type' => 'decklist',
            'id' => $decklist_id,
			'content' => $this->renderView('AppBundle:Widgets:decklist_overview.html.twig', array(
                'decklist' => $decklist,
                'factions' => $decklist_factions,
				'typeNames' => $typeNames,
            	'factionNames' => $factionNames
			))
		));
	}
}
