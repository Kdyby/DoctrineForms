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
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette;
use Nette\ComponentModel\Component;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface IComponentMapper
{

	const FIELD_NAME = 'field.name';
	const FIELD_NOT_LOAD = 'field.notLoad';
	const ITEMS_TITLE = 'items.title';
	const ITEMS_FILTER = 'items.filter';
	const ITEMS_ORDER = 'items.order';



	/**
	 * @param ClassMetadata $meta
	 * @param Component $component
	 * @param object $entity
	 * @throws \Kdyby\DoctrineForms\InvalidStateException
	 * @return
	 */
	function load(ClassMetadata $meta, Component $component, $entity);



	/**
	 * @param ClassMetadata $meta
	 * @param Component $component
	 * @param object $entity
	 * @throws \Kdyby\DoctrineForms\InvalidStateException
	 * @return
	 */
	function save(ClassMetadata $meta, Component $component, $entity);

}
