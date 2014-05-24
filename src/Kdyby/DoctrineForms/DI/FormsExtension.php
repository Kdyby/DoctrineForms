<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineForms\DI;

use Kdyby;
use Nette;
use Nette\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FormsExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('entityFormMapper'))
			->setClass('Kdyby\DoctrineForms\EntityFormMapper');

		$builder->addDefinition($this->prefix('constraintViolationsMapper'))
			->setClass('Kdyby\DoctrineForms\ConstraintViolationsMapper');

		$builder->addDefinition($this->prefix('controlFactory'))
			->setClass('Kdyby\DoctrineForms\Builder\ControlFactory');

		$builder->addDefinition($this->prefix('builderFactory'))
			->setClass('Kdyby\DoctrineForms\BuilderFactory');
	}



	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('doctrineForms', new FormsExtension());
		};
	}

}

