<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms;

use Doctrine;
use Kdyby;
use Nette;
use Nette\Application\UI;
use Nette\Forms\Container;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ConstraintViolationsMapper extends Nette\Object
{

	/**
	 * @var \Symfony\Component\Validator\Validator\ValidatorInterface
	 */
	private $validator;

	/**
	 * @var \Kdyby\Translation\Translator
	 */
	private $translator;

	/**
	 * @var Doctrine\ORM\EntityManager
	 */
	private $em;



	public function __construct(ValidatorInterface $validator, Kdyby\Translation\Translator $translator, Doctrine\ORM\EntityManager $entityManager)
	{
		$this->validator = $validator;
		$this->translator = $translator;
		$this->em = $entityManager;
	}



	/**
	 * @param Container $container
	 * @param object $entity
	 * @return \Symfony\Component\Validator\ConstraintViolationInterface[]|ConstraintViolationList
	 */
	public function validateContainer(Container $container, $entity)
	{
		if ($entity === NULL) {
			return;
		}

		$meta = $this->em->getClassMetadata(get_class($entity));

		$groups = NULL;
		if ($entity instanceof GroupSequenceProviderInterface) {
			$groups = $entity->getGroupSequence();
		}

		/** @var ConstraintViolationList|ConstraintViolationInterface[] $violations */
		$violations = $this->validator->validate($entity, $groups);
		$this->mapViolationsToForm($container, $violations);

		foreach ($container->getComponents(FALSE, 'Nette\Forms\Container') as $child) {
			/** @var Nette\Forms\Container $child */
			if (!$meta->hasAssociation($field = $child->getName())) {
				continue;
			}

			if ($meta->isSingleValuedAssociation($field)) {
				$this->validateContainer($child, $meta->getFieldValue($entity, $field));

			} else {
				throw new NotImplementedException("To many relation is not yet implemented");
			}
		}
	}



	/**
	 * @param \Nette\Forms\Container $container
	 * @param ConstraintViolationList|ConstraintViolationInterface[] $violations
	 */
	public function mapViolationsToForm(Container $container, ConstraintViolationList $violations)
	{
		foreach ($violations as $violation) {
			$control = $this->findControl($container, $violation);
			$control->addError($this->translator->translate($violation->getMessageTemplate(), $violation->getMessagePluralization(), $violation->getMessageParameters(), 'validators'));
		}
	}



	public function buildClientSideValidations(UI\Form $form, $entity)
	{
		throw new NotImplementedException;
	}



	/**
	 * @param Container $container
	 * @param ConstraintViolationInterface $violation
	 * @return Nette\Forms\IControl|Nette\Forms\Controls\BaseControl|UI\Form
	 */
	private function findControl(Container $container, ConstraintViolationInterface $violation)
	{
		if (!$m = Nette\Utils\Strings::split('.' . $violation->getPropertyPath(), '~([\\.\\[])~')) {
			return $container->getForm(); // apply the error to form
		}

		$control = $container;
		while (($type = array_shift($m)) !== NULL && $control) {
			if (empty($type)) {
				continue;
			}

			$step = array_shift($m);
			if ($type === '[') {
				$step = substr($step, 0, -1);
			}

			$control = $control->getComponent($step, FALSE);
		}

		return $control instanceof Nette\Forms\IControl ? $control : $container->getForm();
	}

}
