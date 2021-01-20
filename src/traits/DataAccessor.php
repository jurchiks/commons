<?php
namespace js\tools\commons\traits;

use js\tools\commons\collections\None;
use js\tools\commons\collections\Option;
use js\tools\commons\collections\Some;

/**
 * This trait adds the ability to access array data in a convenient manner, i.e. by using dot notation for nested
 * arrays. There are also convenience methods for casting to a specific data type.
 * Data can be loaded either by calling the init() method or by overriding the load() method for lazy loading.
 */
trait DataAccessor
{
	private $data = null;
	
	protected function init(array $data)
	{
		$this->data = $data;
	}
	
	protected function load(): array
	{
		return [];
	}
	
	public function isEmpty(): bool
	{
		return empty($this->data);
	}
	
	public function size(): int
	{
		return count($this->data);
	}
	
	public function getAll(): array
	{
		if ($this->data === null)
		{
			$this->data = $this->load();
		}
		
		return $this->data;
	}
	
	public function exists(string $name): bool
	{
		return $this->search($name)->isFound();
	}
	
	/**
	 * @param string $name : the name of the config property to retrieve. Can be dot-separated for access to nested
	 *     properties, e.g. "database.host" will retrieve $config["database"]["host"] if it exists
	 * @param mixed $default : the default value to return if property was not found
	 * @return mixed whatever the config value or default value is
	 */
	public function get(string $name, $default = null)
	{
		return $this->search($name)->getOrElse($default);
	}
	
	public function getInt(string $name, int $default = 0): int
	{
		$value = $this->get($name, $default);
		
		return (is_numeric($value) ? intval($value) : $default);
	}
	
	public function getFloat(string $name, float $default = 0): float
	{
		$value = $this->get($name, $default);
		
		return (is_numeric($value) ? floatval($value) : $default);
	}
	
	public function getString(string $name, string $default = ''): string
	{
		return strval($this->get($name, $default));
	}
	
	public function getBool(string $name, bool $default = false): bool
	{
		return boolval($this->get($name, $default));
	}
	
	public function getArray(string $name, array $default = []): array
	{
		$value = $this->get($name, $default);
		
		return (is_array($value) ? $value : $default);
	}
	
	private function search(string $name): Option
	{
		$data = $this->getAll();
		
		if (isset($data[$name]))
		{
			return new Some($data[$name]);
		}
		
		if (strpos($name, '.') === false)
		{
			return new None();
		}
		
		$parts = explode('.', $name);
		$found = [];
		$value = null;
		
		foreach ($parts as $key)
		{
			if (is_null($value))
			{
				// first level
				if (isset($data[$key]))
				{
					$value = $data[$key];
					$found[$key] = true;
				}
			}
			else if (isset($value[$key]))
			{
				// nested levels, e.g. $name = "database.host"
				$value = $value[$key];
				$found[$key] = true;
			}
		}
		
		if ($parts === array_keys($found))
		{
			return new Some($value);
		}
		else
		{
			return new None();
		}
	}
}
