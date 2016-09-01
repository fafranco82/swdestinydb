<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\OwnedCard;

/**
 * Collection controller.
 *
 */
class CollectionController extends Controller
{

    /**
     * Lists all Card entities.
     *
     */
    public function indexAction()
    {
        

        return $this->render('AppBundle:Collection:index.html.twig', array(
            'collection' => $this->getDoctrine()->getRepository('AppBundle:OwnedCard')->getCollection($this->getUser()->getId())
        ));
    }

    public function saveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository('AppBundle:OwnedCard');

        $changes = (array)json_decode($request->get('changes'));
        foreach($changes as $change)
        {
            $owned = NULL;
            if($change->actualOwned)
            {
                $owned = $repo->getByCardCode($change->code);
            }
            else
            {
                $owned = new OwnedCard();
                $card = $this->getDoctrine()->getRepository('AppBundle:Card')->findByCode($change->code);
                $owned->setCard($card)->setUser($this->getUser());
            }
            $owned->setQuantity($change->owned);
            $em->persist($owned);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('collection_list'));
    }
}
