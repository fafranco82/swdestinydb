<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Model\DecklistManager;
use AppBundle\Entity\Decklist;

class DefaultController extends Controller
{

    public function indexAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        /** 
         * @var $decklist_manager DecklistManager  
         */
        $decklist_manager = $this->get('decklist_manager');
        $decklist_manager->setLimit(1);
        
        $typeNames = [];
        foreach($this->getDoctrine()->getRepository('AppBundle:Type')->findAll() as $type) {
        	$typeNames[$type->getCode()] = $type->getName();
        }

        $factionNames = [];
        foreach($this->getDoctrine()->getRepository('AppBundle:Faction')->findAllAndOrderByName() as $faction) {
            $factionNames[$faction->getCode()] = $faction->getName();
        }
        
        $decklists_by_faction = [];
        $affiliations = $this->getDoctrine()->getRepository('AppBundle:Affiliation')->findBy(['isPrimary' => true], ['code' => 'ASC']);
        $factions = $this->getDoctrine()->getRepository('AppBundle:Faction')->findBy(['isPrimary' => true], ['code' => 'ASC']);

        foreach($factions as $faction)
        {
            $decklists_by_affiliation = [];
            foreach($affiliations as $affiliation) 
            {
                $array = [];
                $array['affiliation'] = $affiliation;
                $array['predominantFaction'] = $faction;

            	$decklist_manager->setAffiliation($affiliation);
                $decklist_manager->setPredominantFaction($faction);
            	$paginator = $decklist_manager->findDecklistsByPopularity();
            	/**
            	 * @var $decklist Decklist
            	 */
                $decklist = $paginator->getIterator()->current();
                
                if($decklist) 
                {
                    $array['decklist'] = $decklist;

                    $characterDeck = $decklist->getSlots()->getCharacterRow();
                    $array['character_deck'] = $characterDeck;

                    $array['count_by_type'] = $decklist->getSlots()->getCountByType();

                    $decklist_factions = $decklist->getSlots()->getCountByFaction();
                    arsort($decklist_factions);
                    $array['factions'] = array_keys(array_filter($decklist_factions, function($v) {
                        return $v > 0;
                    }));

                    $decklists_by_affiliation[] = $array;
                }
            }
            $decklists_by_faction[] = $decklists_by_affiliation;
        }

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');
        
        return $this->render('AppBundle:Default:index.html.twig', [
            'pagetitle' =>  "$game_name Deckbuilder",
            'pagedescription' => "Build your deck for $game_name by $publisher_name. Browse the cards and the thousand of decklists submitted by the community. Publish your own decks and get feedback.",
            'decklists_by_faction' => $decklists_by_faction,
            'typeNames' => $typeNames,
            'factionNames' => $factionNames
        ], $response);
    }

    function rulesAction()
    {
    	$response = new Response();
    	$response->setPublic();
    	$response->setMaxAge($this->container->getParameter('cache_expiration'));

    	$page = $this->renderView('AppBundle:Default:rulesreference.html.twig',
    			array(
                    "pagetitle" => $this->get("translator")->trans("nav.rules"),
                    "pagedescription" => $this->get("translator")->trans("nav.rulesreference")
                )
        );
    	$response->setContent($page);
    	return $response;
    }

    function faqAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $page = $this->renderView('AppBundle:Default:faq.html.twig',
                array(
                    "pagetitle" => $this->get("translator")->trans("nav.faq"),
                    "pagedescription" => $this->get("translator")->trans("nav.faq")
                )
        );
        $response->setContent($page);
        return $response;
    }

    function aboutAction()
    {
    	$response = new Response();
    	$response->setPublic();
    	$response->setMaxAge($this->container->getParameter('cache_expiration'));

    	return $this->render('AppBundle:Default:about.html.twig', array(
    			"pagetitle" => "About",
    			"game_name" => $this->container->getParameter('game_name'),
    	), $response);
    }

    function apiIntroAction()
    {
    	$response = new Response();
    	$response->setPublic();
    	$response->setMaxAge($this->container->getParameter('cache_expiration'));

    	return $this->render('AppBundle:Default:apiIntro.html.twig', array(
    			"pagetitle" => "API",
    			"game_name" => $this->container->getParameter('game_name'),
    			"publisher_name" => $this->container->getParameter('publisher_name'),
    	), $response);
    }

    public function thumbsAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $cards = [];
        foreach($this->getDoctrine()->getRepository('AppBundle:Type')->findAll() as $type)
        {
            $card = $this->getDoctrine()->getRepository('AppBundle:Card')->findByType($type->getCode())[0];
            $cards[] = $this->get('cards_data')->getCardInfo($card, false);
        }

        return $this->render('AppBundle:Default:thumbs.html.twig', array(
            "cards" => $cards
        ), $response);
    }
}
