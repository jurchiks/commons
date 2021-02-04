<?php
namespace js\tools\commons\collections;

/**
 * @template T
 */
final class Some extends Option
{
	/**
	 * @var T
	 */
	private $value;
	
	/**
	 * @param T $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	/**
	 * @return T
	 */
	public function get()
	{
		return $this->value;
	}
}
