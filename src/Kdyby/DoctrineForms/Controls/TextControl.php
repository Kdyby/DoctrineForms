<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms\Controls;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\DoctrineForms\EntityFormMapper;
use Kdyby\DoctrineForms\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Symfony\Component\PropertyAccess\PropertyAccessor;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TextControl extends Nette\Object implements IComponentMapper
{

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;

	/**
	 * @var PropertyAccessor
	 */
	private $accessor;

	/**
	 * @var EntityManager
	 */
	private $em;



	public function __construct(EntityFormMapper $mapper)
	{
		$this->mapper = $mapper;
		$this->em = $this->mapper->getEntityManager();
		$this->accessor = $mapper->getAccessor();
	}



	/**
	 * {@inheritdoc}
	 */
	public function load(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof BaseControl) {
			return FALSE;
		}


		if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {
			$component->setValue($this->accessor->getValue($entity, $name));
			return TRUE;
		}

		if (!$meta->hasAssociation($name)) {
			return FALSE;
		}

		/** @var SelectBox|RadioList|MultiChoiceControl $component */
		if (($component instanceof SelectBox || $component instanceof RadioList || $component instanceof MultiChoiceControl) && !count($component->getItems())) {
			if (!$nameKey = $component->getOption(self::ITEMS_TITLE, FALSE)) {
				$path = $component->lookupPath('Nette\Application\UI\Form');
				throw new Kdyby\DoctrineForms\InvalidStateException(
					'Either specify items for ' . $path . ' yourself, or set the option Kdyby\DoctrineForms\IComponentMapper::ITEMS_TITLE ' .
					'to choose field that will be used as title'
				);
			}

			$criteria = $component->getOption(self::ITEMS_FILTER, array());
			$orderBy = $component->getOption(self::ITEMS_ORDER, array());

			$related = $this->relatedMetadata($entity, $name);
			$items = $this->findPairs($related, $criteria, $orderBy, $nameKey);

			$component->setItems($items);
		}

		if ($relation = $this->accessor->getValue($entity, $name)) {
			if (is_array($relation)) {
				$component->setDefaultValue(array_keys($relation));

			} else {
				$UoW = $this->em->getUnitOfWork();
				$component->setDefaultValue($UoW->getSingleIdentifierValue($relation));
			}
		}

		return TRUE;
	}



	/**
	 * @param string|object $entity
	 * @param string $relationName
	 * @return ClassMetadata|Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	private function relatedMetadata($entity, $relationName)
	{
		$meta = $this->em->getClassMetadata(is_object($entity) ? get_class($entity) : $entity);
		$targetClass = $meta->getAssociationTargetClass($relationName);
		return $this->em->getClassMetadata($targetClass);
	}



	/**
	 * @param ClassMetadata $meta
	 * @param array $criteria
	 * @param array $orderBy
	 * @param string $nameKey
	 * @return array
	 */
	private function findPairs(ClassMetadata $meta, $criteria, $orderBy, $nameKey)
	{
		$repository = $this->em->getRepository($meta->getName());

		if ($repository instanceof Kdyby\Doctrine\EntityDao) {
			return $repository->findPairs($criteria, $nameKey, $orderBy);
		}

		$items = array();
		$idKey = $meta->getSingleIdentifierFieldName();
		foreach ($repository->findBy($criteria, $orderBy) as $entity) {
			$items[$this->accessor->getValue($entity, $idKey)] = $this->accessor->getValue($entity, $nameKey);
		}

		return $items;
	}



	/**
	 * {@inheritdoc}
	 */
	public function save(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof BaseControl) {
			return FALSE;
		}

		if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {
			$component->loadHttpData();
			$this->accessor->setValue($entity, $name, $component->getValue());
			return TRUE;
		}

		if (!$meta->hasAssociation($name)) {
			return FALSE;
		}

		if (!$identifier = $component->getValue()) {
			return FALSE;
		}

		$entityClass = $this->relatedMetadata($entity, $name)->getName();
		$repository = $this->em->getRepository($entityClass);

		if (is_array($identifier)) {
			$property = substr($name, 0, -1);
			foreach ($repository->findAll() as $associatedEntity) {
				if (in_array($associatedEntity->id, $identifier)) {
					$hasMethod = 'has' . ucfirst($property);
					if (!$entity->$hasMethod($associatedEntity)) {
						$addMethod = 'add' . ucfirst($property);
						$entity->$addMethod($associatedEntity);
					}

				} else {
					$removeMethod = 'remove' . ucfirst($property);
					$entity->$removeMethod($associatedEntity);
				}
			}

		} elseif ($relation = $repository->find($identifier)) {
			$meta->setFieldValue($entity, $name, $relation);
		}

		return TRUE;
	}

}
