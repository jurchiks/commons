<?php
namespace js\tools\commons\collections;

class ImmutableMap extends ArrayMap
{
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
	
	public function sort(bool $ascending, int $flags, bool $sortByKeys, bool $preserveKeys): ArrayMap
	{
		return new static($this->sortData($ascending, $flags, $sortByKeys, $preserveKeys, null));
	}
	
	public function sortManual(bool $sortByKeys, bool $preserveKeys, callable $callback): ArrayMap
	{
		return new static($this->sortData(false, 0, $sortByKeys, $preserveKeys, $callback));
	}
}
