<?php
namespace js\tools\commons\collections;

class ImmutableList extends ArrayList
{
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
