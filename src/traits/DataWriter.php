<?php
namespace js\tools\commons\traits;

use InvalidArgumentException;

/**
 * This trait is an extension of the DataAccessor trait and adds data writing functionality.
 */
trait DataWriter
{
	use DataAccessor;
	
	public function set($key, $value)
	{
		$this->getAll(); // ensure the load() method is called first
		
		$container = &$this->data; // this needs to be a pointer in order for the value to be stored correctly
		$parts = self::getKeyParts($key);
		
		if (empty($parts))
		{
			throw new InvalidArgumentException('Key must not be empty');
		}
		
		$index = $parts[0];
		$last = count($parts) - 1;
		
		foreach ($parts as $i => $index)
		{
			if (!is_array($container))
			{
				$container = [$container];
			}
			
			if (!isset($container[$index]))
			{
				$container[$index] = [];
			}
			
			if ($i < $last)
			{
				$container = &$container[$index];
			}
		}
		
		$container[$index] = $value;
	}
}
