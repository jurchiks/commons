<?php
namespace js\tools\commons\traits;

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
		$data = $this->getAll();
		
		if (isset($data[$name]))
		{
			return true;
		}
		
		if (strpos($name, '.') === false)
		{
			return false;
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
		
		unset($value);
		
		return ($parts === array_keys($found));
	}
	
	/**
	 * @param string $name : the name of the config property to retrieve. Can be dot-separated for access to nested
	 *     properties, e.g. "database.host" will retrieve $config["database"]["host"] if it exists
	 * @param mixed $default : the default value to return if property was not found
	 * @return mixed whatever the config value or default value is
	 */
	public function get(string $name, $default = null)
	{
		$data = $this->getAll();
		
		if (isset($data[$name]))
		{
			return $data[$name];
		}
		
		if (strpos($name, '.') === false)
		{
			return $default;
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
		
		return (($parts === array_keys($found)) ? $value : $default);
	}
	
	public function getInt(string $name, int $default = 0): int
	{
		$value = $this->get($name);
		
		return (is_numeric($value) ? intval($value) : $default);
	}
	
	public function getFloat(string $name, float $default = 0): float
	{
		$value = $this->get($name);
		
		return (is_numeric($value) ? floatval($value) : $default);
	}
	
	public function getString(string $name, string $default = ''): string
	{
		$value = $this->get($name);
		
		return (!is_null($value) ? strval($value) : $default);
	}
	
	public function getBool(string $name, bool $default = false): bool
	{
		$value = $this->get($name);
		
		return (!empty($value) ? $value : $default);
	}
	
	public function getArray(string $name, array $default = []): array
	{
		$value = $this->get($name);
		
		return (is_array($value) ? $value : $default);
	}
}
