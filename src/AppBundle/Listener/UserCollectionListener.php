<?php

namespace AppBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\SecurityContext;

class UserCollectionListener
{
	private $twig;
	private $em = null;
	private $securityContext = null;

	public function __construct(\Twig_Environment $twig, EntityManager $entityManager, SecurityContext $securityContext)
	{
		$this->twig = $twig;
		$this->em = $entityManager;
		$this->securityContext = $securityContext;
	}

	/**
	 * If the user is logged in, add the collection to the user
	 *
	 * @param FilterControllerEvent $event An FilterControllerEvent instance
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		//only works on master request (not in embedded requests)
		if($event->isMasterRequest())
		{
			if ($this->securityContext->getToken() && is_object($this->securityContext->getToken()->getUser()))
			{
   				$user = $this->securityContext->getToken()->getUser();
   				$collection = $this->em->getRepository('AppBundle:Collection')->getCollection($user->getId());
   				$this->twig->addGlobal('collection', $collection);
   			}
   		}
   	}
}