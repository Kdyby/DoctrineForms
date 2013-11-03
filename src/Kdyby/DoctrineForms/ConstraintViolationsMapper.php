<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms;

use Kdyby;
use Nette;
use Nette\Application\UI;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\ValidatorInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ConstraintViolationsMapper extends Nette\Object
{

	/**
	 * @var \Symfony\Component\Validator\ValidatorInterface
	 */
	private $validator;

	/**
	 * @var \Kdyby\Translation\Translator
	 */
	private $translator;



	public function __construct(ValidatorInterface $validator, Kdyby\Translation\Translator $translator)
	{
		$this->validator = $validator;
		$this->translator = $translator;
	}



	public function validate(UI\Form $form, $entity)
	{
		$groups = NULL;
		if ($entity instanceof GroupSequenceProviderInterface) {
			$groups = $entity->getGroupSequence();
		}

		/** @var ConstraintViolationList|ConstraintViolationInterface[] $violations */
		$violations = $this->validator->validate($entity, $groups);
		if (count($violations) === 0) {
			return;
		}

		foreach ($violations as $violation) {
			$control = $this->findControl($form, $violation);
			$control->addError($this->translator->translate($violation->getMessageTemplate(), $violation->getMessagePluralization(), $violation->getMessageParameters(), 'validators'));
		}
	}



	/**
	 * @param UI\Form $form
	 * @param ConstraintViolationInterface $violation
	 * @return Nette\Forms\IControl|Nette\Forms\Controls\BaseControl|UI\Form
	 */
	private function findControl(UI\Form $form, ConstraintViolationInterface $violation)
	{
		if (!$m = Nette\Utils\Strings::split('.' . $violation->getPropertyPath(), '~([\\.\\[])~')) {
			return $form; // apply the error to form
		}

		$control = $form;
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

		return $control instanceof Nette\Forms\IControl ? $control : $form;
	}

}
