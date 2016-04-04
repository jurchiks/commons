<?php
namespace js\tools\commons\traits;

/**
 * This trait is an extension of the DataAccessor trait and adds data writing functionality.
 */
trait DataWriter
{
	use DataAccessor;
	
	public function set(string $key, $value)
	{
		$this->getAll(); // ensure the load() method is called first
		
		$container = &$this->data; // this needs to be a pointer in order for the value to be stored correctly
		$index = $key;
		
		if (!isset($container[$index])
			&& (strpos($key, '.') !== false))
		{
			$key = explode('.', $key);
			$last = count($key) - 1;
			
			foreach ($key as $i => $index)
			{
				if (!isset($container[$index]))
				{
					$container[$index] = [];
				}
				
				if ($i < $last)
				{
					$container = &$container[$index];
				}
			}
		}
		
		$container[$index] = $value;
	}
}
