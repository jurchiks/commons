<?php
namespace js\tools\commons\collections;

use InvalidArgumentException;

class MutableList extends ArrayList
{
	// region ArrayAccess methods
	
	public function offsetSet($offset, $value)
	{
		if (!$this->offsetExists($offset))
		{
			throw new InvalidArgumentException(
				'Offset does not exist; only modifications of existing offsets are allowed. Consider using add() instead.'
			);
		}
		
		$this->data[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
		$this->data = array_values($this->data); // Reset indexes.
	}
	
	// endregion
	
	public function append(...$values): ArrayList
	{
		$this->data = array_merge($this->data, $values);
		
		return $this;
	}
	
	public function prepend(...$values): ArrayList
	{
		$this->data = array_merge($values, $this->data);
		
		return $this;
	}
	
	public function map(callable $callback): ArrayList
	{
		$this->data = $this->mapData($callback);
		
		return $this;
	}
	
	public function filter(callable $predicate): ArrayList
	{
		$this->data = $this->filterData($predicate, false);
		
		return $this;
	}
	
	public function group(callable $callback): ArrayMap
	{
		return new MutableMap($this->groupData($callback, false));
	}
	
	public function flatten(): ArrayList
	{
		$this->data = $this->flattenData(false);
		
		return $this;
	}
	
	public function sort(bool $ascending, int $flags = SORT_REGULAR): ArrayList
	{
		$this->data = $this->sortData($ascending, $flags, false, false, null);
		
		return $this;
	}
	
	public function sortManual(callable $callback): ArrayList
	{
		$this->data = $this->sortData(false, 0, false, false, $callback);
		
		return $this;
	}
}
