<?php

/**
 * Test: Kdyby\Doctrine\Builder.
 *
 * @testCase KdybyTests\Doctrine\BuilderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Doctrine
 */

namespace KdybyTests\DoctrineForms;

use Kdyby;
use Kdyby\DoctrineForms\Builder;
use Kdyby\DoctrineForms\BuilderFactory;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette;
use Nette\Application\UI;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/models/cms.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BuilderTest extends ORMTestCase
{

	/**
	 * @var BuilderFactory
	 */
	private $builderFactory;

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;



	protected function setUp()
	{
		$em = $this->createMemoryManager();
		$this->builderFactory = new BuilderFactory($em, $this->mapper = new EntityFormMapper($em), new Builder\ControlFactory());

		Kdyby\DoctrineForms\ToManyContainer::register();
	}



	public function testBuildEntity_propertyList()
	{
		$form = self::buildEntityForm();

		$builder = $this->builderFactory->create($form)->bindEntity(new CmsAddress());
		$builder->buildFields(array('city', 'zip'));
		Assert::true(isset($form['city'], $form['zip']));

		Assert::type('Nette\Forms\Controls\TextInput', $form['city']);
		Assert::type('Nette\Forms\Controls\TextInput', $form['zip']);
		Assert::equal(array($form['city'], $form['zip']), self::formControls($form));
	}



	public function testBuildEntity_propertyList_withoutEntity()
	{
		$form = self::buildEntityForm();

		$builder = $this->builderFactory->create($form)->bindEntityType(__NAMESPACE__ . '\CmsAddress');
		$builder->buildFields(array('city', 'zip'));
		Assert::true(isset($form['city'], $form['zip']));

		Assert::type('Nette\Forms\Controls\TextInput', $form['city']);
		Assert::type('Nette\Forms\Controls\TextInput', $form['zip']);
		Assert::equal(array($form['city'], $form['zip']), self::formControls($form));
	}



	public function testBuildEntity_whitelist()
	{
		$form = self::buildEntityForm();

		$builder = $this->builderFactory->create($form)->bindEntity(new CmsAddress());
		$builder->whitelist('city', 'zip')->buildFields();
		Assert::true(isset($form['city'], $form['zip']));

		Assert::type('Nette\Forms\Controls\TextInput', $form['city']);
		Assert::type('Nette\Forms\Controls\TextInput', $form['zip']);
		Assert::equal(array($form['city'], $form['zip']), self::formControls($form));
	}



	public function testBuildEntity_blacklist()
	{
		$form = self::buildEntityForm();

		$builder = $this->builderFactory->create($form)->bindEntity(new CmsAddress());
		$builder->blacklist('city')->buildFields();
		Assert::true(isset($form['country'], $form['zip']));

		Assert::type('Nette\Forms\Controls\TextInput', $form['country']);
		Assert::type('Nette\Forms\Controls\TextInput', $form['zip']);
		Assert::equal(array($form['country'], $form['zip']), self::formControls($form));
	}



	public function testBuildEntity_relation()
	{
		$form = self::buildEntityForm();

		$builder = $this->builderFactory->create($form)->bindEntity(new CmsAddress());
		$builder->buildFields(array('user.username'));
		Assert::true(isset($form['user']['username']));

		Assert::type('Nette\Forms\Container', $form['user']);
		Assert::type('Nette\Forms\Controls\TextInput', $form['user']['username']);
		Assert::equal(array($form['user']['username']), self::formControls($form));
	}



	public function testBuildEntity_relationOfRelation()
	{
		$form = self::buildEntityForm();

		$builder = $this->builderFactory->create($form)->bindEntity(new CmsAddress());
		$builder->buildFields(array('user.email.email'));
		Assert::true(isset($form['user']['email']['email']));

		Assert::type('Nette\Forms\Container', $form['user']);
		Assert::type('Nette\Forms\Container', $form['user']['email']);
		Assert::type('Nette\Forms\Controls\TextInput', $form['user']['email']['email']);
		Assert::equal(array($form['user']['email']['email']), self::formControls($form));
	}



	/**
	 * @param Nette\Forms\Container $form
	 * @return Nette\Forms\IControl[]
	 */
	private static function formControls(Nette\Forms\Container $form)
	{
		return array_values(iterator_to_array($form->getComponents(TRUE, 'Nette\Forms\IControl')));
	}

}


\run(new BuilderTest());
