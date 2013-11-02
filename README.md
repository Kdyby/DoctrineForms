Kdyby/DoctrineForms [![Build Status](https://secure.travis-ci.org/Kdyby/DoctrineForms.png?branch=master)](http://travis-ci.org/Kdyby/DoctrineForms)
===========================


Requirements
------------

Kdyby/DoctrineForms requires PHP 5.3.2 with pdo extension.

- [Nette Framework 2.0.x](https://github.com/nette/nette)
- [Kdyby/Doctrine](https://github.com/Kdyby/Doctrine)
- [Kdyby/Validator](https://github.com/Kdyby/Validator)


Installation
------------

The best way to install Kdyby/DoctrineForms is using  [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/doctrine-forms
```

But if you're using development version of Nette, you have to specify the development Kdyby dependencies.

```js
"require": {
	"nette/nette": "@dev",
	"kdyby/annotations": "@dev",
	"kdyby/doctrine-cache": "@dev",
	"kdyby/events": "@dev",
	"kdyby/console": "@dev",
	"kdyby/doctrine": "@dev"
	"kdyby/validator": "@dev"
	"kdyby/doctrine-forms": "@dev"
}
```

and now run the update

```sh
$ composer update
```

More information can be found at [detailed documentation](https://github.com/Kdyby/DoctrineForms/blob/master/docs/en/index.md#installation).


-----

Homepage [http://www.kdyby.org](http://www.kdyby.org) and repository [http://github.com/Kdyby/DoctrineForms](http://github.com/Kdyby/DoctrineForms).
