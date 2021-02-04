<?php
namespace js\tools\commons\collections;

use RuntimeException;

class ImmutableList extends ArrayList
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
	
	public function append(...$values): ArrayList
	{
		$data = array_merge($this->data, $values);
		
		return new self($data);
	}
	
	public function prepend(...$values): ArrayList
	{
		$data = array_merge($values, $this->data);
		
		return new self($data);
	}
	
	public function map(callable $callback): ArrayList
	{
		return new self($this->mapData($callback));
	}
	
	public function filter(callable $predicate): ArrayList
	{
		return new self($this->filterData($predicate, false));
	}
	
	public function group(callable $callback): ArrayMap
	{
		return new ImmutableMap($this->groupData($callback, false));
	}
	
	public function flatten(): ArrayList
	{
		return new self($this->flattenData(false));
	}
	
	public function sort(bool $ascending, int $flags = SORT_REGULAR): ArrayList
	{
		return new self($this->sortData($ascending, $flags, false, false));
	}
	
	public function sortManual(callable $callback): ArrayList
	{
		return new self($this->sortData(false, 0, false, false, $callback));
	}
}
