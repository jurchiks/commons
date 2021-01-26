<?php
namespace js\tools\commons\collections;

class MutableMap extends ArrayMap
{
	// region ArrayAccess methods
	
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}
	
	public function offsetUnset($offset)
	{
		$this->unset($offset);
	}
	
	// endregion
	
	public function set($key, $value): ArrayMap
	{
		$this->data[$key] = $value;
		
		return $this;
	}
	
	public function unset(...$keys): ArrayMap
	{
		foreach ($keys as $key)
		{
			unset($this->data[$key]);
		}
		
		return $this;
	}
	
	public function map(callable $callback): ArrayMap
	{
		$this->data = $this->mapData($callback);
		
		return $this;
	}
	
	public function filter(callable $predicate, bool $preserveKeys = false): ArrayMap
	{
		$this->data = $this->filterData($predicate, $preserveKeys);
		
		return $this;
	}
	
	public function group(callable $callback, bool $preserveKeys = false): ArrayMap
	{
		$this->data = $this->groupData($callback, $preserveKeys);
		
		return $this;
	}
	
	public function flatten(bool $preserveKeys = false): ArrayMap
	{
		$this->data = $this->flattenData($preserveKeys);
		
		return $this;
	}
	
	public function sort(
		bool $ascending = true,
		int $flags = SORT_REGULAR,
		bool $sortByKeys = false,
		bool $preserveKeys = true
	): ArrayMap
	{
		$this->data = $this->sortData($ascending, $flags, $sortByKeys, $preserveKeys);
		
		return $this;
	}
	
	public function sortManual(callable $callback, bool $sortByKeys = false, bool $preserveKeys = true): ArrayMap
	{
		$this->data = $this->sortData(false, 0, $sortByKeys, $preserveKeys, $callback);
		
		return $this;
	}
}
