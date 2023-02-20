# DESCRIPTION

This is a php trait for Singleton pattern.
Stable Release: 0.1.0

## Usage:

```
<?php

class YourClass
{
    use Baxtian\SingletonTrait;

    /*
    code of your class
    */
}

$obj = YourClass::get_instance();
```

For direct injection

```
<?php

// Dependencies to be used
class ClassA {
	function echo() {
		echo "A";
	};
}
class ClassB {
	function echo() {
		echo "B";
	};
}

class YourClass
{
	use Baxtian\SingletonTrait;

	// Create class constructor with a variable that will 
	// contain -if any- the list of arguments
	public function __construct($arguments = [])
	{
		// Array of attributes linked to the class
		$classes = [
			'class_a' => ClassA::class,
			'class_b' => ClassB::class,
		];

		$this->set_dependencies($arguments, $classes);
	}

	// Function that uses an injected dependency
	public function foo() {
		$this->dependency('class_a')->echo();
		$this->dependency('class_b')->echo();
	}
}
```

To pass a specific instance (as a mock for testing)

```
$foo = YourClass::get_instance([
	'class_a' => new ClassA(), 
	'class_b' => new ClassB()
]);
```

## Mantainers

Juan Sebastián Echeverry <baxtian.echeverry@gmail.com>

## Changelog

## 0.6.1

* Use get_dependency as dependency constructor where available.

## 0.6

* Add direct injection methods.

## 0.5

* In case of using this class in multiple providers, allow Composer to set which file to use by default.

## 0.4.1

* Using recomendations for wakeup and clone.

## 0.4

* Using PSR naming recomendation.

## 0.3

* Bugs fixed

## 0.2

* Allow to use PHP8.0

## 0.1

* First stable release
