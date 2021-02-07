<?php
namespace js\tools\commons\collections;

use InvalidArgumentException;

class ArrayHelper
{
	/**
	 * Get a single entry from an array.
	 *
	 * @param array $array
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get($array, 'foo')</li>
	 * <li>get($array, ['foo', 0])</li>
	 * <li>get($array, 'foo.bar')</li>
	 * </ul>
	 * @return Option {@link Some} if the value was retrieved, {@link None} otherwise.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public static function get(array $array, $key): Option
	{
		// Special case for plain string key access.
		// Necessary because down the line the string is split into parts.
		if (is_string($key) && isset($array[$key]))
		{
			return Option::of($array[$key]);
		}
		
		$parts = self::normalizeKey($key);
		
		if (empty($parts))
		{
			return Option::empty();
		}
		
		$value = $array;
		
		foreach ($parts as $part)
		{
			if (is_array($value) && array_key_exists($part, $value))
			{
				$value = $value[$part];
			}
			else
			{
				return Option::empty();
			}
		}
		
		return Option::of($value);
	}
	
	/**
	 * Set a single entry in an array.
	 *
	 * @param array $array
	 * @param int|string|array<int|string> $key The key/index of the property to set.
	 * Examples:
	 * <ul>
	 * <li>set($array, 'foo', $value)</li>
	 * <li>set($array, ['foo', 0], $value)</li>
	 * <li>set($array, 'foo.bar', $value)</li>
	 * </ul>
	 * @param mixed $value
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public static function set(array &$array, $key, $value): void
	{
		$container = &$array; // This needs to be a pointer in order for the value to be stored correctly.
		$parts = self::normalizeKey($key);
		
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
	
	/**
	 * @param mixed $key
	 * @return array<int|string>
	 * @throws InvalidArgumentException
	 */
	private static function normalizeKey($key): array
	{
		$validateKey = function ($key): void
		{
			if (!is_int($key) && !is_string($key))
			{
				throw new InvalidArgumentException('Key must be int or string, got ' . gettype($key));
			}
		};
		
		if (is_array($key))
		{
			array_walk($key, $validateKey);
			
			return array_values($key);
		}
		else if (is_string($key))
		{
			return explode('.', $key);
		}
		else
		{
			$validateKey($key);
			
			return [$key];
		}
	}
}
