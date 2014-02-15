<?php

/**
 * Test: Kdyby\Doctrine\ControlFactory.
 *
 * @testCase KdybyTests\Doctrine\ControlFactoryTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Doctrine
 */

namespace KdybyTests\DoctrineForms;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\DoctrineForms\Builder;
use Nette;
use Nette\Application\UI;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/models/cms.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ControlFactoryTest extends ORMTestCase
{

	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var Builder\ControlFactory
	 */
	private $controlFactory;



	protected function setUp()
	{
		$this->em = $this->createMemoryManager();
		$this->controlFactory = new Builder\ControlFactory();
	}



	public function dataCreate()
	{
		$fields = array();
		$fields[] = array('CmsAddress', 'city', new Nette\Forms\Controls\TextInput('entity.cmsAddress.city'));
		$fields[] = array('CmsArticle', 'text', new Nette\Forms\Controls\TextArea('entity.cmsArticle.text'));

		return $fields;
	}



	/**
	 * @dataProvider dataCreate
	 */
	public function testCreate($entity, $property, $expected)
	{
		$class = $this->em->getClassMetadata(__NAMESPACE__ . '\\' . $entity);
		$control = $this->controlFactory->create($class, $class->getFieldMapping($property));
		Assert::equal($expected, $control);
	}


}


\run(new ControlFactoryTest());
