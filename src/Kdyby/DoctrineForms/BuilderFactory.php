<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BuilderFactory extends Nette\Object
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;

	/**
	 * @var Builder\ControlFactory
	 */
	private $controlFactory;



	public function __construct(EntityManager $em, EntityFormMapper $mapper, Builder\ControlFactory $controlFactory)
	{
		$this->em = $em;
		$this->mapper = $mapper;
		$this->controlFactory = $controlFactory;
	}



	public function create(Nette\Application\UI\Form $form)
	{
		return new Builder\EntityBuilder($form, $this->mapper, $this->controlFactory, $this->em);
	}

}
