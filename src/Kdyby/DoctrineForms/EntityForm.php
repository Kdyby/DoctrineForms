<?php // lint >= 5.4

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
use Nette\Forms\Controls\BaseControl;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method ToManyContainer toMany($name, $containerFactory = NULL, $entityFactory = NULL)
 * @method onSubmit(UI\Form $self)
 * @method onError(UI\Form $self)
 */
trait EntityForm
{

	/**
	 * @var EntityFormMapper
	 */
	private $entityMapper;

	/**
	 * @var ConstraintViolationsMapper
	 */
	private $violationsMapper;

	/**
	 * @var BuilderFactory
	 */
	private $formBuilderFactory;

	/**
	 * @var Builder\EntityBuilder
	 */
	private $formBuilder;

	/**
	 * @var object
	 */
	private $entity;



	/**
	 * @param EntityFormMapper $mapper
	 * @return EntityForm|UI\Form|
	 */
	public function injectEntityMapper(EntityFormMapper $mapper)
	{
		$this->entityMapper = $mapper;
		return $this;
	}



	/**
	 * @return \Kdyby\DoctrineForms\EntityFormMapper
	 */
	public function getEntityMapper()
	{
		if ($this->entityMapper === NULL) {
			$this->entityMapper = $this->getServiceLocator()->getByType('Kdyby\DoctrineForms\EntityFormMapper');
		}

		return $this->entityMapper;
	}



	/**
	 * @param ConstraintViolationsMapper $mapper
	 * @return EntityForm|UI\Form
	 */
	public function injectValidator(ConstraintViolationsMapper $mapper)
	{
		$this->violationsMapper = $mapper;
		return $this;
	}



	/**
	 * @return ConstraintViolationsMapper
	 */
	public function getViolationsMapper()
	{
		if ($this->violationsMapper === NULL) {
			$this->violationsMapper = $this->getServiceLocator()->getByType('Kdyby\DoctrineForms\ConstraintViolationsMapper');
		}

		return $this->violationsMapper;
	}



	/**
	 * @param BuilderFactory $factory
	 * @return EntityForm|UI\Form
	 */
	public function injectBuilderFactory(BuilderFactory $factory)
	{
		$this->formBuilderFactory = $factory;
		return $this;
	}



	/**
	 * @return Builder\EntityBuilder
	 */
	public function getBuilder()
	{
		if ($this->formBuilder === NULL) {
			if ($this->formBuilderFactory === NULL) {
				$this->formBuilderFactory = $this->getServiceLocator()->getByType('Kdyby\DoctrineForms\BuilderFactory');
			}

			/** @var EntityForm|UI\Form $this */
			$this->formBuilder = $this->formBuilderFactory->create($this);
		}

		return $this->formBuilder;
	}



	/**
	 * @param object $entity
	 * @return EntityForm|UI\Form
	 */
	public function bindEntity($entity)
	{
		$this->entity = $entity;

		/** @var EntityForm|UI\Form $this */
		$this->getEntityMapper()->load($entity, $this);

		return $this;
	}



	/**
	 * Always returns the first created field;
	 *
	 * @param array|string $field
	 * @return BaseControl|UI\Form|EntityForm
	 */
	public function add($field)
	{
		/** @var EntityForm|UI\Form $this */

		$fields = is_array($field) ? $field : func_get_args();
		$this->getBuilder()->buildFields($fields);

		return $this->getComponent(reset($fields));
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	public function fireEvents()
	{
		/** @var EntityForm|UI\Form $this */

		if (!$submittedBy = $this->isSubmitted()) {
			return;
		}

		$this->validate();

		if ($this->isValid()) {
			$this->getEntityMapper()->save($this->entity, $this);
		}

		$this->getViolationsMapper()->validateContainer($this, $this->entity);

		if ($submittedBy instanceof Nette\Forms\ISubmitterControl) {
			if ($this->isValid()) {
				$submittedBy->onClick($submittedBy);
			} else {
				$submittedBy->onInvalidClick($submittedBy);
			}
		}

		if ($this->onSuccess) {
			foreach ($this->onSuccess as $handler) {
				if (!$this->isValid()) {
					$this->onError($this);
					break;
				}
				Nette\Utils\Callback::invoke($handler, $this);
			}
		} elseif (!$this->isValid()) {
			$this->onError($this);
		}
		$this->onSubmit($this);
	}



	/**
	 * @return Nette\DI\Container|\SystemContainer
	 */
	private function getServiceLocator()
	{
		/** @var EntityForm|UI\Form $this */
		/** @var UI\Presenter $presenter */
		$presenter = $this->lookup('Nette\Application\UI\Presenter');

		return $presenter->getContext();
	}

}
