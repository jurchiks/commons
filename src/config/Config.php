<?php
namespace js\tools\commons\config;

use js\tools\commons\exceptions\ConfigException;
use js\tools\commons\traits\DataAccessor;

/**
 * This class provides basic support for application configuration.
 */
class Config
{
	use DataAccessor;
	
	/**
	 * @param string $pathToFile
	 * @param bool $isJson
	 * @return self
	 * @throws ConfigException If the file is not readable or returns invalid data.
	 */
	public static function loadFromFile(string $pathToFile, bool $isJson = false): self
	{
		if (!is_readable($pathToFile))
		{
			throw new ConfigException('Config file is not available: ' . $pathToFile);
		}
		
		if ($isJson)
		{
			$data = json_decode(file_get_contents($pathToFile), true);
		}
		else
		{
			$data = require $pathToFile;
		}
		
		if (!is_array($data))
		{
			throw new ConfigException('Config file must return an array');
		}
		
		return new static($data);
	}
	
	/**
	 * @param string $json
	 * @return self
	 * @throws ConfigException If JSON does not contain an array or object.
	 */
	public static function loadFromJson(string $json): self
	{
		$data = json_decode($json, true);
		
		if (!is_array($data))
		{
			throw new ConfigException('Json data must contain an array');
		}
		
		return new static($data);
	}
	
	public static function loadFromArray(array $data): self
	{
		return new static($data);
	}
	
	private function __construct(array $data)
	{
		$this->init($data);
	}
}
