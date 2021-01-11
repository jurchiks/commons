<?php
namespace js\tools\commons\collections;

use RuntimeException;

class ImmutableList extends ArrayList
{
	// ============== ArrayAccess methods - START ==============
	
	public function offsetSet($offset, $value)
	{
		throw new RuntimeException('Direct modification of immutable collections is not allowed.');
	}
	
	public function offsetUnset($offset)
	{
		throw new RuntimeException('Direct modification of immutable collections is not allowed.');
	}
	
	// ============== ArrayAccess methods - END ==============
	
	public function append(...$values): ArrayList
	{
		$data = array_merge($this->data, $values);
		
		return new static($data);
	}
	
	public function prepend(...$values): ArrayList
	{
		$data = array_merge($values, $this->data);
		
		return new static($data);
	}
	
	public function map(callable $callback): ArrayList
	{
		return new static($this->mapData($callback));
	}
	
	public function filter(callable $predicate): ArrayList
	{
		return new static($this->filterData($predicate, false));
	}
	
	public function group(callable $callback): ArrayMap
	{
		return new ImmutableMap($this->groupData($callback, false));
	}
	
	public function flatten(): ArrayList
	{
		return new static($this->flattenData(false));
	}
	
	public function sort(bool $ascending, int $flags = SORT_REGULAR): ArrayList
	{
		return new static($this->sortData($ascending, $flags, false, false, null));
	}
	
	public function sortManual(callable $callback): ArrayList
	{
		return new static($this->sortData(false, 0, false, false, $callback));
	}
}
