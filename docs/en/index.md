Quickstart
==========

This extension is here to empower Nette Framework forms with [Doctrine 2 ORM](http://www.doctrine-project.org/projects/orm.html) metadata and.


Installation
-----------

The best way to install Kdyby/DoctrineForms is using [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/doctrine-forms
```

And now you have to register the extensions in `app/bootstrap.php`

```php
// add these four lines
Kdyby\Annotations\DI\AnnotationsExtension::register($configurator);
Kdyby\Console\DI\ConsoleExtension::register($configurator);
Kdyby\Events\DI\EventsExtension::register($configurator);
Kdyby\Doctrine\DI\OrmExtension::register($configurator);
Kdyby\Validator\DI\ValidatorExtension::register($configurator);
Kdyby\DoctrineForms\DI\FormsExtension::register($configurator);

return $configurator->createContainer();
```

But if you're using development version of Nette, you have to specify the development Kdyby dependencies.

```js
"require": {
	"nette/nette": "@dev",
	"kdyby/annotations": "@dev",
	"kdyby/doctrine-cache": "@dev",
	"kdyby/events": "@dev",
	"kdyby/console": "@dev",
	"kdyby/doctrine": "@dev",
	"kdyby/validator": "@dev",
	"kdyby/doctrine-forms": "@dev"
}
```

and now run the update

```sh
$ composer update
```

you can also enable the extension using your neon config

```yml
extensions:
	translation: Kdyby\Translation\DI\TranslationExtension
	events: Kdyby\Events\DI\EventsExtension
	console: Kdyby\Console\DI\ConsoleExtension
	annotations: Kdyby\Annotations\DI\AnnotationsExtension
	doctrine: Kdyby\Doctrine\DI\OrmExtension
	validator: Kdyby\Validator\DI\ValidatorExtension
	doctrineForms: Kdyby\DoctrineForms\DI\FormsExtension
```

Please see documentation, on how to configure [Kdyby/Doctrine](https://github.com/Kdyby/Doctrine/blob/master/docs/en/index.md), [Kdyby/Events](https://github.com/Kdyby/Events/blob/master/docs/en/index.md), [Kdyby/Console](https://github.com/Kdyby/Console/blob/master/docs/en/index.md) and [Kdyby/Annotations](https://github.com/Kdyby/Annotations/blob/master/docs/en/index.md).
