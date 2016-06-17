<?php
namespace js\tools\commons\collections;

use InvalidArgumentException;
use Iterator;
use Traversable;

abstract class Collection implements Iterator
{
	protected $data;
	
	/**
	 * @param array|Traversable $data
	 */
	public function __construct($data)
	{
		if (is_array($data))
		{
			$this->data = $data;
		}
		else if ($data instanceof Traversable)
		{
			$this->data = $this->generateData($data);
		}
		else
		{
			throw new InvalidArgumentException(
				'Unsupported data type: ' . is_object($data) ? get_class($data) : gettype($data)
			);
		}
	}
	
	public function __clone()
	{
		return new static($this->data);
	}
	
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
		$predicate = function ($v, $k) use ($value)
		{
			return ($v === $value);
		};
		
		return $this->findAll($predicate, true, $limit);
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
		return $this->findOne($predicate, $findFirst, false);
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
		return $this->findOne($predicate, $findFirst, true);
	}
	
	public function findValues(callable $predicate, int $limit = 0): array
	{
		return $this->findAll($predicate, false, $limit);
	}
	
	public function findKeys(callable $predicate, int $limit = 0): array
	{
		return $this->findAll($predicate, true, $limit);
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
	
	public abstract function mutable();
	
	public abstract function immutable();
	
	protected final function findOne(callable $predicate, bool $findFirst, bool $findKey): Option
	{
		$found = false;
		$data = null;
		
		foreach ($this->data as $key => $value)
		{
			if ($predicate($value, $key) === true)
			{
				$found = true;
				$data = $findKey ? $key : $value;
				
				if ($findFirst)
				{
					break;
				}
			}
		}
		
		return new Option($data, $found);
	}
	
	protected final function findAll(callable $predicate, bool $findKey, int $limit = 0): array
	{
		$data = [];
		
		foreach ($this->data as $key => $value)
		{
			if ($predicate($value, $key) === true)
			{
				$data[] = $findKey ? $key : $value;
			}
		}
		
		if (($limit > 0) && ($limit < count($data)))
		{
			return array_slice($data, 0, $limit);
		}
		else if (($limit < 0) && (-$limit < count($data)))
		{
			return array_slice($data, $limit);
		}
		else
		{
			return $data;
		}
	}
	
	protected final function findData(callable $callback, bool $findFirst, bool $findKey): Option
	{
		$found = false;
		$data = null;
		
		foreach ($this->data as $key => $value)
		{
			if ($callback($value, $key) === true)
			{
				$found = true;
				$data = $findKey ? $key : $value;
				
				if ($findFirst)
				{
					break;
				}
			}
		}
		
		return new Option($data, $found);
	}
	
	protected final function generateData(Traversable $source): array
	{
		$newData = [];
		
		foreach ($source as $key => $value)
		{
			$newData[$key] = $value;
		}
		
		return $newData;
	}
	
	protected final function mapData(callable $callback): array
	{
		$newData = [];
		
		foreach ($this->data as $key => $value)
		{
			$newData[$key] = $callback($value, $key);
		}
		
		return $newData;
	}
	
	protected final function filterData(callable $callback, bool $preserveKeys): array
	{
		$newData = [];
		
		foreach ($this->data as $key => $value)
		{
			$keep = $callback($value, $key);
			
			if (!is_bool($keep))
			{
				throw new InvalidArgumentException('filter() callback must return a boolean');
			}
			
			if ($keep)
			{
				if ($preserveKeys)
				{
					$newData[$key] = $value;
				}
				else
				{
					$newData[] = $value;
				}
			}
		}
		
		return $newData;
	}
	
	protected final function groupData(callable $callback, bool $preserveKeys): array
	{
		$newData = [];
		
		foreach ($this->data as $key => $value)
		{
			$newKey = $callback($value, $key);
			
			if (!is_scalar($newKey))
			{
				throw new InvalidArgumentException('group() callback must return a scalar value');
			}
			
			if ($preserveKeys)
			{
				$newData[$newKey][$key] = $value;
			}
			else
			{
				$newData[$newKey][] = $value;
			}
		}
		
		return $newData;
	}
	
	protected final function flattenData(bool $preserveKeys): array
	{
		$newData = [];
		
		$callback = function ($value, $key) use (&$newData, $preserveKeys)
		{
			if ($preserveKeys)
			{
				$newData[$key] = $value;
			}
			else
			{
				$newData[] = $value;
			}
		};
		
		array_walk_recursive($this->data, $callback);
		
		return $newData;
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
