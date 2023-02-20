<?php

namespace Baxtian;

trait Singleton
{
	/**
	 * @var Singleton reference to singleton instance
	 */
	private static $instance;

	/**
	 * gets the instance via lazy initialization (created on first usage).
	 *
	 * @return self
	 */
	public static function get_instance()
	{
		if (static::$instance === null) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * The Singleton's constructor should always be private to prevent direct
	 * construction calls with the `new` operator.
	 *
	 * @return void
	 */
	protected function __construct()
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
}
