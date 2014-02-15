<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms\Builder;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby;
use Kdyby\DoctrineForms\UnexpectedValueException;
use Nette;
use Nette\Forms\Controls;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ControlFactory extends Nette\Object
{

	public function create(ClassMetadata $class, array $mapping)
	{
		/** @var Controls\BaseControl|Nette\Forms\IControl $control */

		// todo: overriding?

		if (method_exists($this, $method = 'create' . ucFirst($mapping['type']))) {
			$control = $this->{$method}($class, $mapping);

		} else {
			$control = new Controls\TextInput();
		}

		if (!$control instanceof Nette\Forms\IControl) {
			throw new UnexpectedValueException("Form control must implement Nette\\Forms\\IControl, but " . is_object($control) ? get_class($control) : gettype($control) . ' was given');
		}

		if ($control instanceof Controls\BaseControl) {
			$control->caption = $this->defaultControlName($control, $class, $mapping);
		}

		return $control;
	}



	protected function createText(ClassMetadata $class, array $mapping)
	{
		return new Controls\TextArea();
	}



	protected function defaultControlName(Controls\BaseControl $control, ClassMetadata $class, array $mapping)
	{
		return 'entity.' . lcFirst($class->getReflectionClass()->getShortName()) . '.' . $mapping['fieldName'];
	}


//	private $controlTypes = array(
//		Type::TARRAY => 'Doctrine\DBAL\Types\ArrayType',
//		Type::SIMPLE_ARRAY => 'Doctrine\DBAL\Types\SimpleArrayType',
//		Type::JSON_ARRAY => 'Doctrine\DBAL\Types\JsonArrayType',
//		Type::OBJECT => 'Doctrine\DBAL\Types\ObjectType',
//		Type::DATETIME => 'Doctrine\DBAL\Types\DateTimeType',
//		Type::DATETIMETZ => 'Doctrine\DBAL\Types\DateTimeTzType',
//		Type::DATE => 'Doctrine\DBAL\Types\DateType',
//		Type::TIME => 'Doctrine\DBAL\Types\TimeType',
//		Type::BLOB => 'Doctrine\DBAL\Types\BlobType',
//		Type::GUID => 'Doctrine\DBAL\Types\GuidType',
//	);

}
