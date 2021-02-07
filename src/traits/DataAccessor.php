<?php
namespace js\tools\commons\traits;

use InvalidArgumentException;
use js\tools\commons\collections\ArrayHelper;

/**
 * This trait adds the ability to access array data in a convenient manner,
 * e.g. by using dot notation or a list of keys for nested arrays.
 * There are also convenience methods for casting to a specific data type.
 * Data can be loaded either by calling the init() method
 *  or by overriding the load() method for lazy loading.
 */
trait DataAccessor
{
	private ?array $data = null;
	
	protected function init(array $data): void
	{
		$this->data = $data;
	}
	
	protected function load(): array
	{
		return [];
	}
	
	public function isEmpty(): bool
	{
		return empty($this->getAll());
	}
	
	public function size(): int
	{
		return count($this->getAll());
	}
	
	public function getAll(): array
	{
		if ($this->data === null)
		{
			$this->data = $this->load();
		}
		
		return $this->data;
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to search for.
	 * Examples:
	 * <ul>
	 * <li>exists('foo')</li>
	 * <li>exists(['foo', 0])</li>
	 * <li>exists('foo.bar')</li>
	 * </ul>
	 * @return bool whether or not the key was found
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function exists($key): bool
	{
		return ArrayHelper::get($this->getAll(), $key)->isFound();
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get('foo')</li>
	 * <li>get(['foo', 0])</li>
	 * <li>get('foo.bar', 'not found')</li>
	 * </ul>
	 * @param mixed $default The default value to return if property was not found.
	 * @return mixed Whatever the found value or default value is.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function get($key, $default = null)
	{
		return ArrayHelper::get($this->getAll(), $key)->getOrElse($default);
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get('foo')</li>
	 * <li>get(['foo', 0])</li>
	 * <li>get('foo.bar', 5)</li>
	 * </ul>
	 * @param int $default The default value to return if property was not found.
	 * @return int Whatever the found value or default value is.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function getInt($key, int $default = 0): int
	{
		$value = $this->get($key, $default);
		
		return (is_numeric($value) ? intval($value) : $default);
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get('foo')</li>
	 * <li>get(['foo', 0])</li>
	 * <li>get('foo.bar', 5.0)</li>
	 * </ul>
	 * @param float $default The default value to return if property was not found.
	 * @return float Whatever the found value or default value is.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function getFloat($key, float $default = 0): float
	{
		$value = $this->get($key, $default);
		
		return (is_numeric($value) ? floatval($value) : $default);
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get('foo')</li>
	 * <li>get(['foo', 0])</li>
	 * <li>get('foo.bar', 'not found')</li>
	 * </ul>
	 * @param string $default The default value to return if property was not found.
	 * @return string Whatever the found value or default value is.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function getString($key, string $default = ''): string
	{
		return strval($this->get($key, $default));
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get('foo')</li>
	 * <li>get(['foo', 0])</li>
	 * <li>get('foo.bar', false)</li>
	 * </ul>
	 * @param bool $default The default value to return if property was not found.
	 * @return bool Whatever the found value or default value is.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function getBool($key, bool $default = false): bool
	{
		return boolval($this->get($key, $default));
	}
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to retrieve.
	 * Examples:
	 * <ul>
	 * <li>get('foo')</li>
	 * <li>get(['foo', 0])</li>
	 * <li>get('foo.bar', [1, 2, 3])</li>
	 * </ul>
	 * @param array $default The default value to return if property was not found.
	 * @return array Whatever the found value or default value is.
	 * @throws InvalidArgumentException If $key is invalid.
	 */
	public function getArray($key, array $default = []): array
	{
		$value = $this->get($key, $default);
		
		return (is_array($value) ? $value : $default);
	}
}
