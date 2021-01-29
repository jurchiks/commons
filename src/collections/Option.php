<?php
namespace js\tools\commons\collections;

abstract class Option
{
	public static function empty(): None
	{
		return new None();
	}
	
	public static function of($value): Some
	{
		return new Some($value);
	}
	
	public function isEmpty(): bool
	{
		return ($this instanceof None);
	}
	
	public function isFound(): bool
	{
		return ($this instanceof Some);
	}
	
	public function getOrElse($default)
	{
		return ($this->isFound() ? $this->get() : $default);
	}
	
	public abstract function get();
}
