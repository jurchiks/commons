<?php
namespace js\tools\commons\collections;

abstract class ArrayList extends Collection
{
	public function __construct($data)
	{
		parent::__construct($data);
		
		$this->data = array_values($this->data);
	}
	
	public abstract function append(...$values): ArrayList;
	
	public abstract function prepend(...$values): ArrayList;
	
	public function remove($value): ArrayList
	{
		return $this->filter(fn ($v) => ($v !== $value));
	}
	
	/**
	 * Clone this collection into another, mutable collection.
	 *
	 * @return MutableList a mutable collection containing the same data as this collection
	 */
	public function mutable(): MutableList
	{
		return new MutableList($this->data);
	}
	
	/**
	 * Clone this collection into another, immutable collection.
	 *
	 * @return ImmutableList an immutable collection containing the same data as this collection
	 */
	public function immutable(): ImmutableList
	{
		return new ImmutableList($this->data);
	}
	
	/**
	 * Copy the data of this list into a map. Keys are preserved.
	 *
	 * @param bool $mutable : if true, will return a MutableMap, otherwise an ImmutableMap
	 * @return ArrayMap
	 */
	public function toMap(bool $mutable): ArrayMap
	{
		if ($mutable)
		{
			return new MutableMap($this->data);
		}
		else
		{
			return new ImmutableMap($this->data);
		}
	}
	
	/**
	 * Modify items in the list. Callback returns the modified value.
	 *
	 * @param callable $callback : the callback function to apply to each item in the list.
	 * Callback signature - ($value) => mixed
	 * @return ArrayList
	 */
	public abstract function map(callable $callback): ArrayList;
	
	/**
	 * Filter items in the list. Callback returns whether to keep the item in the list or remove it.
	 *
	 * @param callable $predicate : the callback function to apply to each item in the list.
	 * Callback signature - ($value) => bool
	 * @return ArrayList
	 */
	public abstract function filter(callable $predicate): ArrayList;
	
	/**
	 * Split items into groups. Callback returns the name/key of the group. Note that this returns a map, not a list!
	 *
	 * @param callable $callback : the callback function to apply to each item in the list.
	 * Callback signature - ($value) => scalar
	 * @return ArrayMap
	 */
	public abstract function group(callable $callback): ArrayMap;
	
	/**
	 * Flatten nested list into a single-level list.
	 *
	 * @return ArrayList
	 */
	public abstract function flatten(): ArrayList;
	
	/**
	 * Sort the list using built-in comparison functions.
	 *
	 * @param bool $ascending : true if the list values are to be sorted in ascending order, false otherwise
	 * @param int $flags : one of the following flags:
	 * <ul>
	 * <li>SORT_REGULAR - compare items normally (don't change types)</li>
	 * <li>SORT_NUMERIC - compare items numerically</li>
	 * <li>SORT_STRING - compare items as strings</li>
	 * <li>SORT_LOCALE_STRING - compare items as strings, based on the current locale</li>
	 * <li>SORT_NATURAL - compare items as strings using "natural ordering"</li>
	 * <li>SORT_FLAG_CASE - can be combined with SORT_STRING or SORT_NATURAL to sort strings case-insensitively</li>
	 * </ul>
	 * @return ArrayList
	 */
	public abstract function sort(bool $ascending, int $flags = SORT_REGULAR): ArrayList;
	
	/**
	 * Sort the list using a custom comparison function.
	 * Callback returns the standard string comparison values (-1, 0, 1).
	 *
	 * @param callable $callback : the callback function to determine the sort order.
	 * Callback signature - ($value) => int
	 * @return ArrayList
	 */
	public abstract function sortManual(callable $callback): ArrayList;
	
	/**
	 * Reduce the list to a single value using a user-provided callback.
	 * @param callable $callback : the callback function to apply.
	 * Callback signature - ($value, $previous) => mixed
	 * @param mixed $initialValue : the initial value to provide for parameter $previous
	 * @return mixed
	 */
	public function reduce(callable $callback, $initialValue = null)
	{
		$data = $initialValue;
		
		foreach ($this->data as $value)
		{
			$data = $callback($value, $data);
		}
		
		return $data;
	}
}
