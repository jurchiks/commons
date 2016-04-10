<?php
namespace js\tools\commons\collections;

use InvalidArgumentException;

class Option
{
	private $value;
	private $isFound;
	
	public function __construct($value, bool $isFound)
	{
		$this->value = $value;
		$this->isFound = $isFound;
	}
	
	public function get()
	{
		if (!$this->isFound)
		{
			throw new InvalidArgumentException('Option does not have a value; consider using getOrElse()');
		}
		
		return $this->value;
	}
	
	public function getOrElse($default)
	{
		return ($this->isFound ? $this->value : $default);
	}
	
	public function isFound()
	{
		return $this->isFound;
	}
}
