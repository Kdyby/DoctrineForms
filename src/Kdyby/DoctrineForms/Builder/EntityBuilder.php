<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Kdyby;
use Kdyby\DoctrineForms\EntityFormMapper;
use Kdyby\DoctrineForms\InvalidArgumentException;
use Kdyby\DoctrineForms\InvalidStateException;
use Kdyby\DoctrineForms\NotImplementedException;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class EntityBuilder extends Nette\Object
{

	const MODE_WHITELIST = 'whitelist';
	const MODE_BLACKLIST = 'blacklist';

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;

	/**
	 * @var \Nette\Forms\Container
	 */
	private $container;

	/**
	 * @var string
	 */
	private $mode = self::MODE_WHITELIST;

	/**
	 * @var array
	 */
	private $fields = array();

	/**
	 * @var EntityBuilder[]
	 */
	private $relationBuilders = array();

	/**
	 * @var object
	 */
	private $entity;

	/**
	 * @var ClassMetadata
	 */
	private $metadata;

	/**
	 * @var ControlFactory
	 */
	private $controlFactory;



	public function __construct(Nette\Forms\Container $container, EntityFormMapper $mapper, ControlFactory $controlFactory = NULL, EntityManager $em = NULL)
	{
		$this->container = $container;
		$this->mapper = $mapper;
		$this->em = $em ?: $mapper->getEntityManager();
		$this->controlFactory = $controlFactory ?: new ControlFactory();

		/** @var Nette\Application\UI\Form|Kdyby\DoctrineForms\EntityForm $form */
		if (method_exists($form = $container->getForm(FALSE), 'injectEntityMapper')) {
			$form->injectEntityMapper($this->mapper);
		}
	}



	/**
	 * @param object $entity
	 * @throws InvalidArgumentException
	 * @return EntityBuilder
	 */
	public function bindEntity($entity)
	{
		if (!is_object($entity)) {
			throw new InvalidArgumentException('Expected object, ' . gettype($entity) . ' given');
		}

		$this->bindEntityType(get_class($entity));
		$this->entity = $entity;

		return $this;
	}



	/**
	 * @param string $type
	 * @throws \Kdyby\DoctrineForms\InvalidArgumentException
	 * @throws \Kdyby\DoctrineForms\InvalidStateException
	 * @return EntityBuilder
	 */
	public function bindEntityType($type)
	{
		if ($this->metadata !== NULL && $type !== $this->getMetadata()->getName()) {
			throw new InvalidStateException("You cannot change type of EntityBuilder to $type, there is already " . $this->getMetadata()->getName());
		}

		if (!class_exists($type)) {
			throw new InvalidArgumentException("Class $type doesn't exists or cannot be autoloaded");
		}

		if (!$this->metadata = $this->em->getClassMetadata($type)) {
			throw new InvalidArgumentException("Object $type is not a valid entity");
		}

		return $this;
	}



	/**
	 * @throws InvalidArgumentException
	 * @return \Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getMetadata()
	{
		if (!$this->metadata) {
			throw new InvalidArgumentException('Please provide an entity using method Builder::bindEntity()');
		}

		return $this->metadata;
	}



	/**
	 * @param array|string $fields
	 * @return EntityBuilder
	 */
	public function whitelist($fields)
	{
		$this->setMode(self::MODE_WHITELIST);
		$this->fields = is_array($fields) ? $fields : func_get_args();

		return $this;
	}



	/**
	 * @param array|string $fields
	 * @return EntityBuilder
	 */
	public function blacklist($fields)
	{
		$this->setMode(self::MODE_BLACKLIST);
		$this->fields = is_array($fields) ? $fields : func_get_args();

		return $this;
	}



	/**
	 * @param string $mode
	 * @return EntityBuilder
	 */
	public function setMode($mode)
	{
		$this->mode = $mode === self::MODE_WHITELIST ? self::MODE_WHITELIST : self::MODE_BLACKLIST;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function isModeWhitelist()
	{
		return $this->mode === self::MODE_WHITELIST;
	}



	/**
	 * @return bool
	 */
	public function isModeBlacklist()
	{
		return $this->mode === self::MODE_BLACKLIST;
	}



	/**
	 * @param array|string $fields
	 * @return Nette\Application\UI\Form|Nette\Forms\Container
	 */
	public function buildFields($fields = array())
	{
		if (empty($fields)) {
			if ($this->isModeWhitelist()) {
				$fields = $this->fields;

			} else {
				$class = $this->getMetadata();
				$fields = array_diff($class->getFieldNames(), $this->fields, (array) $class->getIdentifierFieldNames());

				$isRelation = function ($f) { return strpos($f, '.'); };
				if (array_filter($this->fields, $isRelation)) {
					throw new NotImplementedException("Sorry but blacklisting relation fields is not yet implemented, use \$builder->relationBuilder(..)->blacklist(..)->buildFields();");
				}
			}

		} elseif (!is_array($fields)) {
			$fields = func_get_args();
		}

		foreach (array_unique($fields) as $name) {
			$this->buildField($name);
		}

		return $this->container;
	}



	public function buildField($name)
	{
		if (count($relation = explode('.', $name, 2)) == 2) {
			return $this->relationBuilder($relation[0])->buildField($relation[1]);
		}

		try {
			$mapping = $this->getMetadata()->getFieldMapping($name);
		} catch (MappingException $e) {
			throw new InvalidArgumentException($e->getMessage(), 0, $e);
		}

		$control = $this->controlFactory->create($this->getMetadata(), $mapping);
		$this->container->addComponent($control, $name);

		$this->mapper->load($this->entity, $control);

		return $control;
	}



	/**
	 * @param string $name
	 * @param array $fields
	 * @return Nette\Application\UI\Form|Nette\Forms\Container
	 */
	public function buildRelation($name, array $fields = array())
	{
		return $this->relationBuilder($name)->buildFields($fields);
	}



	/**
	 * @param string $name
	 * @throws InvalidArgumentException
	 * @throws NotImplementedException
	 * @return EntityBuilder
	 */
	public function relationBuilder($name)
	{
		$class = $this->getMetadata();

		if (!$class->hasAssociation($name)) {
			throw new InvalidArgumentException("Entity {$this->metadata->name} has no association '$name'.");
		}

		if (isset($this->relationBuilders[$name])) {
			return $this->relationBuilders[$name];
		}

		if ($class->isSingleValuedAssociation($name)) {
			if (!$this->container->getComponent($name, FALSE)) {
				$this->container->addComponent(new Nette\Forms\Container(), $name);
			}

			$builder = new EntityBuilder($this->container[$name], $this->mapper, $this->controlFactory, $this->em);

			if ($this->entity && ($relation = $class->getFieldValue($this->entity, $name))) {
				$builder->bindEntity($relation);

			} else {
				$builder->bindEntityType($class->getAssociationTargetClass($name));
			}

			return $this->relationBuilders[$name] = $builder;

		} else {
			throw new NotImplementedException;
		}
	}


}
