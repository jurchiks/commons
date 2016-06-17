<?php
namespace js\tools\commons\collections;

use ArrayAccess;
use InvalidArgumentException;

class MutableList extends ArrayList implements ArrayAccess
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
		if (!$this->offsetExists($offset))
		{
			throw new InvalidArgumentException('offset does not exist; only modifications are allowed. Consider using add() instead.');
		}
		
		$this->data[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
	
	// ============== ArrayAccess methods - END ==============
	
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
		$this->flattenData(false);
		
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
