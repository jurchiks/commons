<?php
namespace js\tools\commons\traits;

/**
 * This trait is an extension of the StaticDataAccessor trait and adds data writing functionality.
 */
trait StaticDataWriter
{
	use StaticDataAccessor;
	
	public static function set(string $key, $value)
	{
		self::getAll(); // ensure the load() method is called first
		
		$container = &self::$data; // this needs to be a pointer in order for the value to be stored correctly
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
