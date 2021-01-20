<?php
namespace js\tools\commons\collections;

abstract class Option
{
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
