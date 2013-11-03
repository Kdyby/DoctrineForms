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
		$this->createMemoryManager();

		/** @var \Symfony\Component\Validator\ValidatorInterface $validator */
		$validator = $this->serviceLocator->getByType('Symfony\Component\Validator\ValidatorInterface');

		/** @var \Kdyby\Translation\Translator $translator */
		$translator = $this->serviceLocator->getByType('Kdyby\Translation\Translator');
		$translator->setLocale('cs');

		$this->mapper = new ConstraintViolationsMapper($validator, $translator);
	}



	public function testValidate()
	{
		$form = new UI\Form;
		$form->addText('topic');

		$article = new CmsArticle();

		$this->mapper->validate($form, $article);

		Tester\Assert::same(array(
			'Tato hodnota nesmí být null.'
		), $form->getAllErrors());

		Tester\Assert::same(array(
			'Tato hodnota nesmí být null.'
		), $form['topic']->getErrors());

	}

}

\run(new ConstraintViolationsMapperTest());
