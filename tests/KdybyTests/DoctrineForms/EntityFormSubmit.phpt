<?php // lint >= 5.4

/**
 * Test: Kdyby\Doctrine\ControlFactory.
 *
 * @testCase KdybyTests\Doctrine\EntityFormSubmitTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Doctrine
 */

namespace KdybyTests\DoctrineForms;

use Kdyby;
use Kdyby\DoctrineForms\Builder;
use Kdyby\DoctrineForms\EntityForm;
use KdybyTests\DoctrineForms\CmsAddress;
use Nette;
use Nette\Application\UI;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/models/cms.php';



/**
 * @author Tomáš Votruba <tomas.vot@gmail.com>
 */
class EntityFormSubmitTest extends ORMTestCase
{

	/**
	 * @var EntityForm|UI\Form
	 */
	private $entityForm;


	protected function setUp()
	{
		$this->createMemoryManager();
		$this->entityForm = $this->buildEntityForm();
	}


	public function testEntityFormInstance()
	{
		Assert::type('Nette\Application\UI\Form', $this->entityForm);
		Assert::null($this->entityForm->getEntity());
	}


	/**
	 * @throws \Nette\InvalidStateException
	 */
	public function testBindAfterAttachmentOnly()
	{
		$this->entityForm->bindEntity(new CmsAddress);
	}


	public function testBind()
	{
		$this->attachToPresenter($this->entityForm);
		$this->entityForm->bindEntity(new CmsAddress);
		Assert::type('KdybyTests\DoctrineForms\CmsAddress', $this->entityForm->getEntity());
	}


	/**
	 * @throws Kdyby\DoctrineForms\InvalidArgumentException
	 */
	public function testSendBeforeBind()
	{
		$this->attachToPresenter($this->entityForm, array('city' => 'Brno'));
	}


	public function testSendForm()
	{
		$presenter = new PresenterWithComponentMock;
		$this->serviceLocator->callMethod(array($presenter, 'injectPrimary'));

		$dataToSubmit = array('action' => 'default', 'do' => 'addressEntityControl-form-submit', 'city' => 'Brno');
		$request = new Nette\Application\Request('default', 'POST', $dataToSubmit, $dataToSubmit);
		$presenter->run($request);

		$entityAfterSubmit = $presenter['addressEntityControl']->getSubmitResult();
		Assert::same('Brno' , $entityAfterSubmit->city);
	}

}


class PresenterWithComponentMock extends UI\Presenter
{

	protected function createComponentAddressEntityControl()
	{
		return new AddressEntityControl;
	}


	public function renderDefault()
	{
		$this->terminate();
	}

}


class AddressEntityControl extends UI\Control
{

	/**
	 * @var CmsAddress
	 */
	private $submitResult;


	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);
		if (!$obj instanceof \Nette\Application\UI\Presenter) {
			return;
		}

		$this['form']->bindEntity(new CmsAddress);
	}


	/**
	 * @return AddressEntityForm
	 */
	protected function createComponentForm()
	{
		$form = ORMTestCase::buildEntityForm();
		$form->addText('city');
		$form->addSubmit('send');
		$form->onSuccess[] = $this->processForm;
		return $form;
	}


	public function processForm($form)
	{
		$this->submitResult = $form->getEntity();
	}


	public function getSubmitResult()
	{
		return $this->submitResult;
	}

}


\run(new EntityFormSubmitTest);
