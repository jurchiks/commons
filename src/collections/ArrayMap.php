<?php
namespace js\tools\commons\collections;

abstract class ArrayMap extends Collection
{
	/**
	 * @param int|string $key
	 * @param mixed $value
	 * @return ArrayMap
	 */
	public abstract function set($key, $value): ArrayMap;
	
	/**
	 * @param int|string ...$keys
	 * @return ArrayMap
	 */
	public abstract function unset(...$keys): ArrayMap;
	
	/**
	 * @param mixed $value
	 * @return ArrayMap
	 */
	public function remove($value): ArrayMap
	{
		return $this->filter(fn ($v) => ($v !== $value), true);
	}
	
	/**
	 * Clone this map into another, mutable map.
	 *
	 * @return MutableMap A mutable map containing the same data as this map.
	 * @see toImmutable()
	 * @see toMutableList()
	 * @see toImmutableList()
	 */
	public function toMutable(): MutableMap
	{
		return new MutableMap($this->data);
	}
	
	/**
	 * Clone this map into another, immutable map.
	 *
	 * @return ImmutableMap An immutable map containing the same data as this map.
	 * @see toMutable()
	 * @see toMutableList()
	 * @see toImmutableList()
	 */
	public function toImmutable(): ImmutableMap
	{
		return new ImmutableMap($this->data);
	}
	
	/**
	 * Copy the values of this map into a list. Keys are not preserved.
	 *
	 * @return MutableList
	 * @see toImmutableList()
	 * @see toMutable()
	 * @see toImmutable()
	 */
	public function toMutableList(): MutableList
	{
		return new MutableList($this->data);
	}
	
	/**
	 * Copy the values of this map into a list. Keys are not preserved.
	 *
	 * @return ImmutableList
	 * @see toMutableList()
	 * @see toMutable()
	 * @see toImmutable()
	 */
	public function toImmutableList(): ImmutableList
	{
		return new ImmutableList($this->data);
	}
	
	/**
	 * @param callable $callback The callback function to apply to each item in the collection.
	 * Callback signature - `(mixed $value, int|string $key): mixed`.
	 * @return ArrayMap
	 */
	public abstract function map(callable $callback): ArrayMap;
	
	/**
	 * @param callable $predicate The callback function to apply to each item in the collection.
	 * Callback signature - `(mixed $value, int|string $key): bool`.
	 * @param bool $preserveKeys If true, the original keys of the values will be preserved.
	 * @return ArrayMap
	 */
	public abstract function filter(callable $predicate, bool $preserveKeys = false): ArrayMap;
	
	/**
	 * Group items together.
	 *
	 * @param callable $callback The callback function to apply to each item in the collection.
	 * Callback signature - `(mixed $value, int|string $key): int|string`.
	 * @param bool $preserveKeys If true, the original keys of the values will be preserved in the newly grouped arrays.
	 * @return ArrayMap A map containing the mapping of $callback return values => [matching items].
	 */
	public abstract function group(callable $callback, bool $preserveKeys = false): ArrayMap;
	
	/**
	 * Flatten nested collections into a single-level collection.
	 *
	 * @param bool $preserveKeys If true, the original keys of the values will be maintained in the newly flattened
	 * array. Note that this may cause loss of data if the same key exists in multiple nested arrays. In this case,
	 * the last element with the duplicate key is the resulting value.
	 * @return ArrayMap
	 */
	public abstract function flatten(bool $preserveKeys = false): ArrayMap;
	
	/**
	 * Sort the collection using built-in comparison functions.
	 *
	 * @param bool $ascending True if the values are to be sorted in ascending order, false otherwise.
	 * @param int $flags One of the following flags:
	 * <ul>
	 * <li>SORT_REGULAR - compare items normally (don't change types)</li>
	 * <li>SORT_NUMERIC - compare items numerically</li>
	 * <li>SORT_STRING - compare items as strings</li>
	 * <li>SORT_LOCALE_STRING - compare items as strings, based on the current locale</li>
	 * <li>SORT_NATURAL - compare items as strings using "natural ordering"</li>
	 * <li>SORT_FLAG_CASE - can be combined with SORT_STRING or SORT_NATURAL to sort strings case-insensitively</li>
	 * </ul>
	 * @param bool $sortByKeys If true, the sorting will occur based on keys instead of values.
	 * @param bool $preserveKeys If true, keys will be preserved as the values are reordered.
	 * @return ArrayMap
	 */
	public abstract function sort(
		bool $ascending = true,
		int $flags = SORT_REGULAR,
		bool $sortByKeys = false,
		bool $preserveKeys = true
	): ArrayMap;
	
	/**
	 * Sort the collection using a custom comparison function.
	 * Callback returns the standard string comparison values (-1, 0, 1).
	 *
	 * @param callable $callback The callback function to determine the sort order.
	 * Callback signature - `(mixed $a, mixed $b): int`.
	 * @param bool $sortByKeys If true, the sorting will occur based on keys instead of values.
	 * @param bool $preserveKeys If true, keys will be preserved as the values are reordered.
	 * @return ArrayMap
	 */
	public abstract function sortManual(
		callable $callback,
		bool $sortByKeys = false,
		bool $preserveKeys = true
	): ArrayMap;
	
	/**
	 * Reduce the map to a single value using a user-provided callback.
	 *
	 * @param callable $callback The callback function to apply.
	 * Callback signature - `(mixed $value, int|string $key, mixed $previous): mixed`.
	 * @param mixed $initialValue The initial value to provide for parameter $previous.
	 * @return mixed
	 */
	public function reduce(callable $callback, $initialValue = null)
	{
		$data = $initialValue;
		
		foreach ($this->data as $key => $value)
		{
			$data = $callback($value, $key, $data);
		}
		
		return $data;
	}
}
