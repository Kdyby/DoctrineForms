<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyTests\DoctrineForms;

use Doctrine\ORM\Tools\SchemaTool;
use Kdyby;
use Nette;
use Nette\Application\UI;
use Nette\PhpGenerator as Code;
use Tester;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class ORMTestCase extends Tester\TestCase
{

	/**
	 * @var \Nette\DI\Container|\SystemContainer
	 */
	protected $serviceLocator;



	/**
	 * @return Kdyby\Doctrine\EntityManager
	 */
	protected function createMemoryManager()
	{
		$rootDir = __DIR__ . '/../../';

		$config = new Nette\Configurator();
		$container = $config->setTempDirectory(TEMP_DIR)
			->addConfig(__DIR__ . '/../nette-reset.neon')
			->addConfig(__DIR__ . '/config/memory.neon')
			->addParameters(array(
				'appDir' => $rootDir,
				'wwwDir' => $rootDir,
			))
			->createContainer();
		/** @var Nette\DI\Container $container */

		$em = $container->getByType('Kdyby\Doctrine\EntityManager');
		/** @var Kdyby\Doctrine\EntityManager $em */

		$schemaTool = new SchemaTool($em);
		$schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

		$this->serviceLocator = $container;

		return $em;
	}



	/**
	 * @param string $className
	 * @param array $props
	 * @return object
	 */
	protected function newInstance($className, $props = array())
	{
		return Code\Helpers::createObject($className, $props);
	}



	/**
	 * @param UI\Form $form
	 * @param array $data
	 * @return PresenterMock
	 */
	protected function attachToPresenter(UI\Form $form, $data = array())
	{
		$presenter = new PresenterMock();
		$this->serviceLocator->callMethod(array($presenter, 'injectPrimary'));

		if (!empty($data)) {
			$request = new Nette\Application\Request('fake', 'POST', array('do' => 'form-submit'), array('do' => 'form-submit') + $data);

		} else {
			$request = new Nette\Application\Request('fake', 'POST', array());
		}

		$presenter->run($request);
		$presenter['form'] = $form;

		return $presenter;
	}



	/**
	 * @return UI\Form|Kdyby\DoctrineForms\EntityForm
	 */
	protected static function buildEntityForm()
	{
		$class = __NAMESPACE__ . '\\EntityForm';
		if (class_exists($class, FALSE)) {
			return new $class();
		}

		if (PHP_VERSION_ID >= 50400) {
			eval('namespace ' . __NAMESPACE__ . ' { class EntityForm extends \Nette\Application\UI\Form { use \Kdyby\DoctrineForms\EntityForm; } }');

		} else {
			$trait = file_get_contents(__DIR__ . '/../../../src/Kdyby/DoctrineForms/EntityForm.php');
			$trait = str_replace('namespace Kdyby\DoctrineForms;', 'namespace ' . __NAMESPACE__ . ';', $trait);
			$trait = str_replace("use Kdyby;", "use Kdyby;\n" . 'use Kdyby\DoctrineForms\EntityFormMapper;', $trait);
			$trait = str_replace("trait EntityForm", 'class EntityForm extends UI\Form', $trait);
			eval(substr($trait, 5));
		}

		return new $class();
	}

}



class PresenterMock extends UI\Presenter
{

	protected function startup()
	{
		$this->terminate();
	}

}
