<?php

namespace Baxtian;

trait SingletonTrait
{
	/**
	 * @var instance reference to singleton instance
	 */
	private static $instance;

	/**
	 * @var dependencies_def reference to the class definition of dependences
	 */
	protected $dependencies_def;

	/**
	 * @var dependencies_ins reference to the defined dependences
	 */
	protected $dependencies_ins = [];

	/**
	 * The Singleton's constructor should always be private to prevent direct
	 * construction calls with the `new` operator.
	 *
	 * @return void
	 */
	protected function __construct($arguments = [])
	{
	}

	/**
	 * Prevent the instance from being cloned.
	 *
	 * @return void
	 */
	protected function __clone()
	{
	}

	/**
	 * Prevent from being unserialized.
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		throw new \Exception('Cannot unserialize a singleton.');
	}

	/**
	 * Constructor del importador
	 */
	protected function set_dependencies($arguments = [], $classes = [])
	{
		$this->dependencies_def = $classes;
		foreach ($this->dependencies_def as $key => $class) {
			$this->dependencies_ins[$key] = ($arguments[$key]) ?? null;
		}
	}

	/**
	 * Gets the dependency
	 */

	private function dependency($dependencia)
	{
		// Si ya tenemos valor, retornarlo
		if (!empty($this->dependencies_ins[$dependencia])) {
			return $this->dependencies_ins[$dependencia];
		}

		if (isset($this->dependencies_def[$dependencia])) {
			$class = $this->dependencies_def[$dependencia];
			
			// If the class uses the get_instance method, use it
			if (method_exists($class, 'get_instance')) {
				$this->dependencies_ins[$dependencia] = $class::get_instance();
			} else { // if not, use class constructor intsted
				$this->dependencies_ins[$dependencia] = new $class();
			}

			return $this->dependencies_ins[$dependencia];
		}

		return false;
	}

	/**
	 * Gets the instance via lazy initialization (created on first usage).
	 *
	 * @return self
	 */
	public static function get_instance($arguments = [])
	{
		if (static::$instance === null) {
			static::$instance = new static($arguments);
		}

		return static::$instance;
	}
}
