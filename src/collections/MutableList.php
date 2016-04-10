<?php
namespace js\tools\commons\collections;

class MutableList extends ArrayList
{
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
