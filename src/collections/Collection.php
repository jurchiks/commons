<?php
namespace js\tools\commons\collections;

use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use Traversable;

abstract class Collection implements Iterator, ArrayAccess
{
	protected array $data;
	
	public function __construct(iterable $data)
	{
		$this->data = ($data instanceof Traversable) ? iterator_to_array($data) : $data;
	}
	
	public function __clone()
	{
		/** @psalm-suppress UnsafeInstantiation */
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
	 * @param mixed $value The value to check.
	 * @return bool True if the collection contains the value, false otherwise.
	 * @see getValue()
	 * @see findValue()
	 * @see findValues()
	 */
	public function containsValue($value): bool
	{
		return in_array($value, $this->data, true);
	}
	
	/**
	 * Check if a given key/index exists in this collection.
	 *
	 * @param int|string $key The key to check.
	 * @return bool True if the collection contains a mapping to the key, false otherwise.
	 * @see getKey()
	 * @see getKeys()
	 * @see findKey()
	 * @see findKeys()
	 */
	public function containsKey($key): bool
	{
		return array_key_exists($key, $this->data);
	}
	
	/**
	 * Get a value by its key/index.
	 *
	 * @param int|string $key The key to look for.
	 * @return Option An {@link Option} possibly containing the found value.
	 * @see containsValue()
	 * @see findValue()
	 * @see findValues()
	 */
	public function getValue($key): Option
	{
		if ($this->containsKey($key))
		{
			return Option::of($this->data[$key]);
		}
		else
		{
			return Option::empty();
		}
	}
	
	/**
	 * Get the first key/index of a given value.
	 *
	 * @param mixed $value The value of the key to get.
	 * @return Option An {@link Option} possibly containing the found key.
	 * @see containsKey()
	 * @see getKeys()
	 * @see findKey()
	 * @see findKeys()
	 */
	public function getKey($value): Option
	{
		if ($this->containsValue($value))
		{
			return Option::of(array_search($value, $this->data, true));
		}
		else
		{
			return Option::empty();
		}
	}
	
	/**
	 * Get all keys of a given value.
	 *
	 * @param mixed $value The value whose keys to get.
	 * @param int $limit The maximum amount of keys to get; 0 - no limit, negative value - get the last N keys.
	 * @return array An array containing all keys that were found.
	 * @see containsKey()
	 * @see getKey()
	 * @see findKey()
	 * @see findKeys()
	 */
	public function getKeys($value, int $limit = 0): array
	{
		return $this->find(fn ($v) => ($v === $value), true, $limit);
	}
	
	/**
	 * Find a value that matches the given criteria.
	 *
	 * @param callable $predicate The callback function to use to find the value.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @param bool $findFirst If true, find the first value, otherwise find the last value.
	 * @return Option An {@link Option} possibly containing the found value.
	 * @see containsValue()
	 * @see getValue()
	 * @see findValues()
	 */
	public function findValue(callable $predicate, bool $findFirst = true): Option
	{
		$data = $this->find($predicate, false, $findFirst ? 1 : -1);
		
		return empty($data) ? Option::empty() : Option::of($data[0]);
	}
	
	/**
	 * Find the key of a value that matches the given criteria.
	 *
	 * @param callable $predicate The callback function to use to find the value.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @param bool $findFirst If true, find the first value, otherwise find the last value.
	 * @return Option An {@link Option} possibly containing the found key.
	 * @see containsKey()
	 * @see getKey()
	 * @see getKeys()
	 * @see findKeys()
	 */
	public function findKey(callable $predicate, bool $findFirst = true): Option
	{
		$data = $this->find($predicate, true, $findFirst ? 1 : -1);
		
		return empty($data) ? Option::empty() : Option::of($data[0]);
	}
	
	/**
	 * Find all values that match the given criteria.
	 *
	 * @param callable $predicate The callback function to use to find the value.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @param int $limit Maximum amount of values to return. If positive, will return the first N values, otherwise
	 * the last N values.
	 * @return array An array containing the matches found.
	 * @see containsValue()
	 * @see getValue()
	 * @see findValue()
	 */
	public function findValues(callable $predicate, int $limit = 0): array
	{
		return $this->find($predicate, false, $limit);
	}
	
	/**
	 * Find all keys that match the given criteria.
	 *
	 * @param callable $predicate The callback function to use to find the value.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @param int $limit Maximum amount of keys to return. If positive, will return the first N keys, otherwise
	 * the last N keys.
	 * @return array An array containing the matches found.
	 * @see containsKey()
	 * @see getKey()
	 * @see getKeys()
	 * @see findKey()
	 */
	public function findKeys(callable $predicate, int $limit = 0): array
	{
		return $this->find($predicate, true, $limit);
	}
	
	/**
	 * Find all entries that match the given criteria.
	 *
	 * @param callable $predicate The callback function to use to find the match.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @param bool $findKeys If true, return an array containing only the matched keys; if false,
	 * return matched values; and if NULL - return the original key => value pairs.
	 * @param int $limit Maximum amount of keys to return. If positive, will return the first N keys, otherwise
	 * the last N keys.
	 * @return array An array containing the matches found.
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
			// Iterate in reverse rather than reversing the whole array or trimming all matches to the limit.
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
	
	/**
	 * Iterate over the entries in the collection.
	 *
	 * @param callable $callback The callback function to access each entry.
	 * Callback signature - `(mixed $value, int|string $key): bool|null`.
	 * If the callback returns `true`, the iteration will be stopped immediately.
	 */
	public function each(callable $callback): void
	{
		foreach ($this->data as $key => $value)
		{
			if ($callback($value, $key) === true)
			{
				break;
			}
		}
	}
	
	/**
	 * Check that all entries in the collection match the desired predicate.
	 *
	 * @param callable $predicate The callback function to check each entry.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @return bool True if all entries match, false otherwise.
	 */
	public function every(callable $predicate): bool
	{
		foreach ($this->data as $key => $value)
		{
			if ($predicate($value, $key) === false)
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Check that at least one entry in the collection matches the desired predicate.
	 *
	 * @param callable $predicate The callback function to check each entry.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @return bool True if at least one entry matches, false otherwise.
	 */
	public function some(callable $predicate): bool
	{
		foreach ($this->data as $key => $value)
		{
			if ($predicate($value, $key) === true)
			{
				return true;
			}
		}
		
		return false;
	}
	
	// region Iterator methods
	
	public function rewind(): void
	{
		reset($this->data);
	}
	
	public function valid(): bool
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
	
	public function next(): void
	{
		next($this->data);
	}
	
	// endregion
	
	// Not officially part of the Iterator interface, but good to have just in case.
	public function prev(): void
	{
		prev($this->data);
	}
	
	// region ArrayAccess methods
	
	public function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}
	
	public function offsetGet($offset)
	{
		return ($this->data[$offset] ?? null);
	}
	
	// endregion
	
	/**
	 * @return Collection
	 */
	public abstract function toMutable();
	
	/**
	 * @return Collection
	 */
	public abstract function toImmutable();
	
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
	
	/**
	 * @param callable $callback
	 * @param bool $preserveKeys
	 * @return array[]
	 *
	 * @psalm-return array<array-key, array<array-key, mixed>>
	 */
	protected final function groupData(callable $callback, bool $preserveKeys): array
	{
		$data = [];
		
		foreach ($this->data as $key => $value)
		{
			$groupKey = $callback($value, $key);
			
			if (!is_int($groupKey) && !is_string($groupKey))
			{
				throw new InvalidArgumentException('group() callback must return an int|string');
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
		
		$callback = function ($value, $key) use (&$data, $preserveKeys): void
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
		bool $ascending,
		int $flags,
		bool $sortByKeys,
		bool $preserveKeys,
		callable $callback = null
	): array
	{
		$sortingFunction = self::pickSortingFunction($callback !== null, $ascending, $sortByKeys, $preserveKeys);
		
		$data = $this->data;
		$sortingFunction($data, $callback ?: $flags);
		
		if ($sortByKeys && !$preserveKeys)
		{
			$data = array_values($data);
		}
		
		return $data;
	}
	
	private static function pickSortingFunction(
		bool $useCallback,
		bool $ascending,
		bool $sortByKeys,
		bool $preserveKeys
	): callable
	{
		if ($sortByKeys)
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
		
		if ($useCallback)
		{
			return [
				1 => 'uksort',
				2 => 'uasort',
				3 => 'usort',
			][$type];
		}
		else if ($ascending)
		{
			return [
				1 => 'ksort',
				2 => 'asort',
				3 => 'sort',
			][$type];
		}
		else
		{
			return [
				1 => 'krsort',
				2 => 'arsort',
				3 => 'rsort',
			][$type];
		}
	}
}
