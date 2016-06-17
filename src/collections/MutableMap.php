<?php
namespace js\tools\commons\collections;

use ArrayAccess;

class MutableMap extends ArrayMap implements ArrayAccess
{
	// ============== ArrayAccess methods - START ==============
	
	public function offsetExists($offset): bool
	{
		return array_key_exists($this->data, $offset);
	}
	
	public function offsetGet($offset)
	{
		return ($this->data[$offset] ?? null);
	}
	
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
	
	// ============== ArrayAccess methods - END ==============
	
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
	
	public function sort(bool $ascending, int $flags, bool $sortByKeys, bool $preserveKeys): ArrayMap
	{
		$this->data = $this->sortData($ascending, $flags, $sortByKeys, $preserveKeys, null);
		
		return $this;
	}
	
	public function sortManual(bool $sortByKeys, bool $preserveKeys, callable $callback): ArrayMap
	{
		$this->data = $this->sortData(false, 0, $sortByKeys, $preserveKeys, $callback);
		
		return $this;
	}
}
