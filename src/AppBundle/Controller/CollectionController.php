<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\CollectionSlot;

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
            'collection' => $this->getDoctrine()->getRepository('AppBundle:Collection')->getCollection($this->getUser()->getId())
        ));
    }

    public function saveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $collection = $this->getDoctrine()->getRepository('AppBundle:Collection')->getCollection($this->getUser()->getId());
        
        $changes = (array)json_decode($request->get('changes'));
        foreach($changes as $change)
        {
            $slot = $collection->getSlots()->getSlotByCode($change->code);
            if(!$slot)
            {
                $slot = new CollectionSlot();
                $card = $this->getDoctrine()->getRepository('AppBundle:Card')->findByCode($change->code);
                $slot->setCard($card)->setCollection($collection);
                $collection->addSlot($slot);
            }
            $slot->setQuantity($change->owned->cards);
            $slot->setDice($change->owned->dice);
        }

        $em->persist($collection);
        $em->flush();

        $this->get('session')->getFlashBag()->set('notice', $this->get("translator")->trans("forms.saved"));

        return $this->redirect($this->generateUrl('collection_list'));
    }
}
