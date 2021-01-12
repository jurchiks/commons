<?php
namespace js\tools\commons\collections;

use RuntimeException;

class ImmutableMap extends ArrayMap
{
	// region ArrayAccess methods
	
	public function offsetSet($offset, $value)
	{
		throw new RuntimeException('Direct modification of immutable collections is not allowed.');
	}
	
	public function offsetUnset($offset)
	{
		throw new RuntimeException('Direct modification of immutable collections is not allowed.');
	}
	
	// endregion
	
	public function set($key, $value): ArrayMap
	{
		$data = $this->data;
		$data[$key] = $value;
		
		return new static($data);
	}
	
	public function unset(...$keys): ArrayMap
	{
		$data = $this->data;
		
		foreach ($keys as $key)
		{
			unset($data[$key]);
		}
		
		return new static($data);
	}
	
	public function map(callable $callback): ArrayMap
	{
		return new static($this->mapData($callback));
	}
	
	public function filter(callable $predicate, bool $preserveKeys = false): ArrayMap
	{
		return new static($this->filterData($predicate, $preserveKeys));
	}
	
	public function group(callable $callback, bool $preserveKeys = false): ArrayMap
	{
		return new static($this->groupData($callback, $preserveKeys));
	}
	
	public function flatten(bool $preserveKeys = false): ArrayMap
	{
		return new static($this->flattenData($preserveKeys));
	}
	
	public function sort(
		bool $ascending = true,
		int $flags = SORT_REGULAR,
		bool $sortByKeys = false,
		bool $preserveKeys = true
	): ArrayMap
	{
		return new static($this->sortData($ascending, $flags, $sortByKeys, $preserveKeys, null));
	}
	
	public function sortManual(callable $callback, bool $sortByKeys = false, bool $preserveKeys = true): ArrayMap
	{
		return new static($this->sortData(false, 0, $sortByKeys, $preserveKeys, $callback));
	}
}
