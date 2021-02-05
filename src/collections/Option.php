<?php
namespace js\tools\commons\collections;

abstract class Option
{
	public static function empty(): None
	{
		return new None();
	}
	
	/**
	 * @param mixed $value
	 * @return Some
	 */
	public static function of($value): Some
	{
		return new Some($value);
	}
	
	/**
	 * @param mixed $value
	 * @return Option {@link None} if the value is null, {@link Some} otherwise.
	 */
	public static function ofNullable($value): Option
	{
		return (($value === null)
			? new None()
			: new Some($value));
	}
	
	public function isEmpty(): bool
	{
		return ($this instanceof None);
	}
	
	public function isFound(): bool
	{
		return ($this instanceof Some);
	}
	
	/**
	 * @param mixed $default
	 * @return mixed
	 */
	public function getOrElse($default)
	{
		return ($this->isFound() ? $this->get() : $default);
	}
	
	/**
	 * @return mixed
	 */
	public abstract function get();
}
