<?php

namespace AppBundle\Form;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
	private $class;

	/**
     * @param string $class The User class name
     */
    public function __construct($class, RequestStack $request_stack)
    {
        parent::__construct($class);
        $this->request_stack = $request_stack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $locale = $this->request_stack->getCurrentRequest()->getLocale();

        $builder->add('notificationLocale', 'hidden', array("data" => $locale));
    }

    public function getName()
    {
        return 'app_user_registration';
    }
}