<?php
namespace js\tools\commons\traits;

trait DataAccessor
{
	private $data = [];
	
	protected function init(array $data)
	{
		$this->data = $data;
	}
	
	public function exists(string $name)
	{
		if (isset($this->data[$name]))
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
				if (isset($this->data[$key]))
				{
					$value = $this->data[$key];
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
		if (isset($this->data[$name]))
		{
			return $this->data[$name];
		}
		
		if (strpos($name, '.') === false)
		{
			return $default;
		}
		
		$parts = explode('.', $name);
		$value = null;
		
		foreach ($parts as $key)
		{
			if (is_null($value))
			{
				// first level
				if (isset($this->data[$key]))
				{
					$value = $this->data[$key];
				}
			}
			else if (isset($value[$key]))
			{
				// nested levels, e.g. $name = "database.host"
				$value = $value[$key];
			}
		}
		
		return (is_null($value) ? $default : $value);
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
