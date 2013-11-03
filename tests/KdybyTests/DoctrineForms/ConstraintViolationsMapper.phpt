<?php

/**
 * Test: Kdyby\DoctrineForms\ConstraintViolationsMapper.
 *
 * @testCase KdybyTests\DoctrineForms\ConstraintViolationsMapperTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\DoctrineForms
 */

namespace KdybyTests\DoctrineForms;

use Kdyby;
use Kdyby\DoctrineForms\ConstraintViolationsMapper;
use Nette;
use Nette\Application\UI;
use Tester;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/models/cms.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ConstraintViolationsMapperTest extends ORMTestCase
{

	/**
	 * @var ConstraintViolationsMapper
	 */
	private $mapper;


	protected function setUp()
	{
		$em = $this->createMemoryManager();

		/** @var \Symfony\Component\Validator\ValidatorInterface $validator */
		$validator = $this->serviceLocator->getByType('Symfony\Component\Validator\ValidatorInterface');

		/** @var \Kdyby\Translation\Translator $translator */
		$translator = $this->serviceLocator->getByType('Kdyby\Translation\Translator');
		$translator->setLocale('cs');

		$this->mapper = new ConstraintViolationsMapper($validator, $translator, $em);
	}



	public function testValidate()
	{
		$form = new UI\Form;
		$form->addText('topic');

		$article = new CmsArticle();

		$this->mapper->validateContainer($form, $article);

		Tester\Assert::same(array(), $form->getErrors());

		Tester\Assert::same(array(
			'Tato hodnota nesmí být null.'
		), $form['topic']->getErrors());
	}



	public function testValidate_toOne()
	{
		$form = new UI\Form;
		$form->addText('topic');
		$userContainer = $form->addContainer('user');
		$userContainer->addText('username');

		$article = new CmsArticle();
		$article->user = new CmsUser();

		$this->mapper->validateContainer($form, $article);

		Tester\Assert::same(array(), $form->getErrors());

		Tester\Assert::same(array(
			'Tato hodnota nesmí být null.'
		), $form['topic']->getErrors());

		Tester\Assert::same(array(
			'Tato hodnota nesmí být prázdná.'
		), $userContainer['username']->getErrors());
	}

}

\run(new ConstraintViolationsMapperTest());
