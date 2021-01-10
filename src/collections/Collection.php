<?php
namespace js\tools\commons\collections;

use InvalidArgumentException;
use Iterator;

abstract class Collection implements Iterator
{
	protected $data;
	
	public function __construct(iterable $data)
	{
		if (is_array($data))
		{
			$this->data = $data;
		}
		else
		{
			$this->data = $this->extractData($data);
		}
	}
	
	public function __clone()
	{
		return new static($this->data);
	}
	
	/**
	 * Get all the data contained in this collection.
	 */
	public function get(): array
	{
		return $this->data;
	}
	
	public function size(): int
	{
		return count($this->data);
	}
	
	/**
	 * Check if a given value is contained in this collection.
	 *
	 * @param mixed $value : the value to check
	 * @return bool true if the collection contains the value, false otherwise
	 */
	public function containsValue($value): bool
	{
		return in_array($value, $this->data, true);
	}
	
	/**
	 * Check if a given key/index exists in this collection.
	 *
	 * @param mixed $key : the key to check
	 * @return bool true if the collection contains a mapping to the key, false otherwise
	 */
	public function containsKey($key): bool
	{
		return array_key_exists($key, $this->data);
	}
	
	/**
	 * Get a value by its key/index.
	 *
	 * @param mixed $key : the key to look for
	 * @return Option an Option possibly containing the found value
	 */
	public function getValue($key): Option
	{
		if ($this->containsKey($key))
		{
			return new Option($this->data[$key], true);
		}
		else
		{
			return new Option(null, false);
		}
	}
	
	/**
	 * Get the first key/index of a given value.
	 *
	 * @param mixed $value : the value of the key to get
	 * @return Option an Option possibly containing the found key
	 * @see containsKey
	 * @see getKeys
	 * @see findKey
	 * @see findKeys
	 */
	public function getKey($value): Option
	{
		if ($this->containsValue($value))
		{
			return new Option(array_search($value, $this->data, true), true);
		}
		else
		{
			return new Option(null, false);
		}
	}
	
	/**
	 * Get all keys of a given value.
	 *
	 * @param mixed $value : the value whose keys to get
	 * @param int $limit : the maximum amount of keys to get; 0 - no limit, negative value - get the last N keys
	 * @return array an array containing all keys that were found
	 */
	public function getKeys($value, int $limit = 0): array
	{
		$predicate = function ($v) use ($value)
		{
			return ($v === $value);
		};
		
		return $this->find($predicate, true, $limit);
	}
	
	/**
	 * Find a value that matches the given criteria.
	 *
	 * @param callable $predicate : the callback function to use to find the value.
	 * Callback signature - ($value, $key) => bool
	 * @param bool $findFirst : if true, find the first value, otherwise find the last value
	 * @return Option an Option possibly containing the found value
	 */
	public function findValue(callable $predicate, bool $findFirst = true): Option
	{
		$data = $this->find($predicate, false, $findFirst ? 1 : -1);
		
		return new Option($data[0] ?? null, isset($data[0]));
	}
	
	/**
	 * Find the key of a value that matches the given criteria.
	 *
	 * @param callable $predicate : the callback function to use to find the value.
	 * Callback signature - ($value, $key) => bool
	 * @param bool $findFirst : if true, find the first value, otherwise find the last value
	 * @return Option an Option possibly containing the found key
	 */
	public function findKey(callable $predicate, bool $findFirst = true): Option
	{
		$data = $this->find($predicate, true, $findFirst ? 1 : -1);
		
		return new Option($data[0] ?? null, isset($data[0]));
	}
	
	/**
	 * Find all values that match the given criteria.
	 *
	 * @param callable $predicate : the callback function to use to find the value.
	 * Callback signature - ($value, $key) => bool
	 * @param int $limit : maximum amount of values to return. If positive, will return the first N values, otherwise
	 * the last N values.
	 * @return array an array containing the matches found
	 */
	public function findValues(callable $predicate, int $limit = 0): array
	{
		return $this->find($predicate, false, $limit);
	}
	
	/**
	 * Find all keys that match the given criteria.
	 *
	 * @param callable $predicate : the callback function to use to find the value.
	 * Callback signature - ($value, $key) => bool
	 * @param int $limit : maximum amount of keys to return. If positive, will return the first N keys, otherwise
	 * the last N keys.
	 * @return array an array containing the matches found
	 */
	public function findKeys(callable $predicate, int $limit = 0): array
	{
		return $this->find($predicate, true, $limit);
	}
	
	/**
	 * Find all entries that match the given criteria.
	 *
	 * @param callable $predicate : the callback function to use to find the match.
	 * Callback signature - ($value, $key) => bool
	 * @param bool $findKeys : if true, return an array containing only the matched keys; if false,
	 * return matched values; and if NULL - return the original key => value pairs.
	 * @param int $limit : maximum amount of keys to return. If positive, will return the first N keys, otherwise
	 * the last N keys.
	 * @return array an array containing the matches found
	 */
	public function find(callable $predicate, bool $findKeys = null, int $limit = 0): array
	{
		$data = [];
		$found = 0;
		
		if ($limit >= 0)
		{
			foreach ($this->data as $key => $value)
			{
				if ($predicate($value, $key) === true)
				{
					$found++;
					
					if ($findKeys === null)
					{
						$data[$key] = $value;
					}
					else
					{
						$data[] = $findKeys ? $key : $value;
					}
					
					if ($found === $limit)
					{
						break;
					}
				}
			}
		}
		else
		{
			// iterate in reverse rather than reversing the whole array or trimming all matches to the limit
			end($this->data);
			
			for ($i = 0; $i < count($this->data); $i++)
			{
				$key = $this->key();
				$value = $this->current();
				
				if ($predicate($value, $key) === true)
				{
					$found++;
					
					if ($findKeys === null)
					{
						$data[$key] = $value;
					}
					else
					{
						$data[] = $findKeys ? $key : $value;
					}
					
					if ($found === -$limit)
					{
						break;
					}
				}
				
				$this->prev();
			}
			
			$this->rewind();
			
			// entries need to be reversed otherwise they'll be in the wrong order
			$data = array_reverse($data, ($findKeys === null));
		}
		
		return $data;
	}
	
	public function each(callable $callback)
	{
		foreach ($this->data as $key => $value)
		{
			if ($callback($value, $key) === true)
			{
				break;
			}
		}
	}
	
	// ============== Iterator methods - START ==============
	
	public function rewind()
	{
		reset($this->data);
	}
	
	public function valid()
	{
		// array keys cannot be null; PHP automatically converts null keys to empty strings
		// i.e. [null => false] is actually ['' => false]
		return ($this->key() !== null);
	}
	
	public function key()
	{
		return key($this->data);
	}
	
	public function current()
	{
		return current($this->data);
	}
	
	public function next()
	{
		next($this->data);
	}
	
	// ============== Iterator methods - END ==============
	
	// not officially part of the Iterator interface, but good to have just in case
	public function prev()
	{
		prev($this->data);
	}
	
	public abstract function mutable();
	
	public abstract function immutable();
	
	protected final function extractData(iterable $source): array
	{
		$data = [];
		
		foreach ($source as $key => $value)
		{
			$data[$key] = $value;
		}
		
		return $data;
	}
	
	protected final function mapData(callable $callback): array
	{
		$data = [];
		
		foreach ($this->data as $key => $value)
		{
			$data[$key] = $callback($value, $key);
		}
		
		return $data;
	}
	
	protected final function filterData(callable $callback, bool $preserveKeys): array
	{
		$data = [];
		
		foreach ($this->data as $key => $value)
		{
			if ($callback($value, $key) === true)
			{
				if ($preserveKeys)
				{
					$data[$key] = $value;
				}
				else
				{
					$data[] = $value;
				}
			}
		}
		
		return $data;
	}
	
	protected final function groupData(callable $callback, bool $preserveKeys): array
	{
		$data = [];
		
		foreach ($this->data as $key => $value)
		{
			$groupKey = $callback($value, $key);
			
			if (!is_scalar($groupKey))
			{
				throw new InvalidArgumentException('group() callback must return a scalar value');
			}
			
			if ($preserveKeys)
			{
				$data[$groupKey][$key] = $value;
			}
			else
			{
				$data[$groupKey][] = $value;
			}
		}
		
		return $data;
	}
	
	protected final function flattenData(bool $preserveKeys): array
	{
		$data = [];
		
		$callback = function ($value, $key) use (&$data, $preserveKeys)
		{
			if ($preserveKeys)
			{
				$data[$key] = $value;
			}
			else
			{
				$data[] = $value;
			}
		};
		
		array_walk_recursive($this->data, $callback);
		
		return $data;
	}
	
	protected final function sortData(
		bool $ascending, int $flags, bool $sortByKeys, bool $preserveKeys, callable $callback = null
	): array
	{
		static $map = [
			'callback' => [
				1 => 'uksort',
				2 => 'uasort',
				3 => 'usort',
			],
			'regular'  => [
				true  => [
					1 => 'ksort',
					2 => 'asort',
					3 => 'sort',
				],
				false => [
					1 => 'krsort',
					2 => 'arsort',
					3 => 'rsort',
				],
			],
		];
		
		if ($sortByKeys) // sorting by keys preserves them as well
		{
			$type = 1;
		}
		else if ($preserveKeys)
		{
			$type = 2;
		}
		else
		{
			$type = 3;
		}
		
		$data = $this->data;
		
		if ($callback)
		{
			$map['callback'][$type]($data, $callback);
		}
		else
		{
			$map['regular'][$ascending][$type]($data, $flags);
		}
		
		return $data;
	}
}
