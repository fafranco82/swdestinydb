<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
            'starters' => $this->getDoctrine()->getRepository('AppBundle:StarterPack')->findAll()
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

    public function exportAction(Request $request)
    {
        $user = $this->getUser();
        $translator = $this->get('translator');
        $collection = $this->getDoctrine()->getRepository('AppBundle:Collection')->getCollection($user->getId());
        $cards = $this->getDoctrine()->getRepository('AppBundle:Card')->findAll();

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()
            ->setCreator($user->getUsername())
            //->setLastModifiedBy($lastModified->format('Y-m-d'))
            ->setTitle($translator->trans("collection.export.title", ["user" => $user->getUsername()]))
        ;
        $phpActiveSheet = $phpExcelObject->setActiveSheetIndex(0);
        $phpActiveSheet->setTitle($translator->trans("card.info.collection"));

        //header
        $headers = [
            $translator->trans("card.info.set"),
            "#",
            $translator->trans("card.info.unique"),
            $translator->trans("card.info.name"),
            $translator->trans("card.info.subtitle"),
            $translator->trans("collection.cards"),
            $translator->trans("collection.dice"),
            $translator->trans("card.info.affiliation"),
            $translator->trans("card.info.faction"),
            $translator->trans("card.info.type"),
            $translator->trans("card.info.rarity"),
            $translator->trans("card.info.points"),
            $translator->trans("card.info.health"),
            $translator->trans("card.info.cost"),
            $translator->trans("card.info.has_die")
        ];
        foreach($headers as $colIndex => $header)
        {
            $phpCell = $phpActiveSheet->getCellByColumnAndRow($colIndex, 1);
            //print_r(get_class_methods($phpCell->getStyle()->getFont())); die();
            $phpCell->getStyle()->applyFromArray([
                'fill' => ['type' => \PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['argb' => 'FF0000FF']],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]
            ]);
            $phpCell->setValue($header);
        }

        $rowIndex = 2;
        foreach($cards as $card)
        {
            $slot = $collection->getSlots()->getSlotByCode($card->getCode());

            $values = [
                $card->getSet()->getName(),
                $card->getPosition(),
                $card->getIsUnique(),
                $card->getName(),
                $card->getSubtitle(),
                $slot ? $slot->getQuantity() : 0,
                $card->getHasDie() ? ($slot ? $slot->getDice() : 0) : "",
                $card->getAffiliation()->getName(),
                $card->getFaction()->getName(),
                $card->getType()->getName(),
                $card->getRarity()->getName(),
                $card->getPoints(),
                $card->getHealth(),
                $card->getCost(),
                $card->getHasDie()
            ];
            
            foreach($values as $colIndex => $value)
            {
                $phpActiveSheet->getCellByColumnAndRow($colIndex, $rowIndex)->setValue($value);
            }

            $rowIndex++;
        }
        $phpActiveSheet->setAutoFilter($phpActiveSheet->calculateWorksheetDimension());
        foreach(range('A','O') as $columnID)
        {
            $phpActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($translator->trans("collection.export.filename", ["user" => $user->getUsername()])) . '.xlsx'
        ));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
        return $response;
    }
}
