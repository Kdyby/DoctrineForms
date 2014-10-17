<?php

/**
 * Test: Kdyby\Doctrine\EntityFormMapper.
 *
 * @testCase KdybyTests\Doctrine\EntityFormMapperTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Doctrine
 */

namespace KdybyTests\DoctrineForms;

use Kdyby;
use Kdyby\DoctrineForms\EntityFormMapper;
use Kdyby\DoctrineForms\IComponentMapper;
use Nette;
use Nette\Application\UI;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/models/cms.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class EntityFormMapperTest extends ORMTestCase
{

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;



	protected function setUp()
	{
		$em = $this->createMemoryManager();
		$this->mapper = new EntityFormMapper($em);

		Kdyby\DoctrineForms\ToManyContainer::register();
	}



	public function testBasic_text()
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);

		$name = $form->addText('name');

		$entity = new CmsGroup('Robot');
		$form->bindEntity($entity);

		Assert::same('Robot', $name->getValue());

		$this->attachToPresenter($form, array('name' => 'Human'));
		Assert::same('Human', $entity->name);
	}



	public function testRelation_toOne()
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);

		$name = $form->addText('name');
		$addressContainer = $form->addContainer('address');
		$city = $addressContainer->addText('city');

		$entity = new CmsUser('Robot');
		$entity->setAddress(new CmsAddress('Brno'));
		$form->bindEntity($entity);

		Assert::same('Robot', $name->getValue());
		Assert::same('Brno', $city->getValue());

		$this->attachToPresenter($form, array('name' => 'Human', 'address' => array('city' => 'Praha')));
		Assert::same('Human', $entity->name);
		Assert::same('Praha', $entity->address->city);
	}



	public function testRelation_toOne_completeRelation()
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);

		$name = $form->addText('name');
		$addressContainer = $form->addContainer('address');
		$city = $addressContainer->addText('city');

		$entity = new CmsUser('Robot');
		Assert::null($entity->address);

		$form->bindEntity($entity);

		Assert::same('Robot', $name->getValue());
		Assert::same('', $city->getValue());

		$this->attachToPresenter($form, array('name' => 'Human', 'address' => array('city' => 'Praha')));
		Assert::same('Human', $entity->name);
		Assert::true($entity->address instanceof CmsAddress);
		Assert::same('Praha', $entity->address->city);
	}



	public function getRelation_toOne_itemsLoadDataProvider()
	{
		return array(
			array(
				'name'
			),
			array(
				function (CmsUser $user) {
					return $user->name;
				},
			),
		);
	}



	/**
	 * @dataProvider getRelation_toOne_itemsLoadDataProvider
	 */
	public function testRelation_toOne_itemsLoad($itemsTitle)
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);
		$em = $this->mapper->getEntityManager();
		$usersDao = $em->getDao(__NAMESPACE__ . '\\CmsUser');

		$usersDao->save(array(
			new CmsUser('DG'),
			new CmsUser('Juzna'),
			new CmsUser('HosipLan'),
		));

		$form->addText('topic');
		$author = $form->addSelect('user')
			->setOption(IComponentMapper::ITEMS_TITLE, $itemsTitle);

		$article = new CmsArticle('Nette');
		$form->bindEntity($article);

		Assert::same(array(1 => 'DG', 2 => 'Juzna', 3 => 'HosipLan'), $author->items);

		$this->attachToPresenter($form, array('topic' => 'Nette Framework', 'user' => 2));
		Assert::same('Nette Framework', $article->topic);
		Assert::same('Juzna', $article->user->name);
	}



	public function testRelation_toMany()
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);

		$name = $form->addText('name');

		/** @var Kdyby\DoctrineForms\ToManyContainer $articlesContainer */
		$articlesContainer = $form->toMany('articles', function (Nette\Forms\Container $article) {
			$article->addText('topic');
		});

		$entity = new CmsUser('Robot');
		$entity->addArticle(new CmsArticle('Doctrine'));
		$entity->addArticle(new CmsArticle('Nette'));

		Assert::true(iterator_count($articlesContainer->getComponents()) === 0);

		$form->bindEntity($entity);

		Assert::same('Robot', $name->getValue());
		list($doctrineContainer, $netteContainer) = array_values(iterator_to_array($articlesContainer->getComponents()));

		Assert::same('Doctrine', $doctrineContainer['topic']->value);
		Assert::same('Nette', $netteContainer['topic']->value);
	}



	/**
	 * @throws Kdyby\DoctrineForms\NotImplementedException
	 */
	public function testRelation_toManyNotImplemented()
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);
		/** @var Kdyby\DoctrineForms\ToManyContainer $articlesContainer */
		$form->toMany('articles', function (Nette\Forms\Container $article) {
			$article->addText('topic');
		});
		$entity = new CmsUser('Robot');
		$entity->addArticle(new CmsArticle('Doctrine'));
		$entity->addArticle(new CmsArticle('Nette'));

		$form->bindEntity($entity);

		$this->attachToPresenter($form, array('name' => 'Human', 'articles' => array('_new_0' => array('topic' => 'Dibi'), '_new_1' => array('topic' => 'Zend'))));
	}



	public function testRename()
	{
		$form = self::buildEntityForm()->injectEntityMapper($this->mapper);

		$name = $form->addCheckbox('surname')
			->setOption(IComponentMapper::FIELD_NAME, 'name');

		$entity = new CmsGroup(TRUE);
		$form->bindEntity($entity);

		Assert::same(TRUE, $name->getValue());

		$this->attachToPresenter($form, array('name' => FALSE));
		Assert::same(FALSE, $entity->name);
	}

}


\run(new EntityFormMapperTest());
