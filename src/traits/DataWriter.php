<?php
namespace js\tools\commons\traits;

use js\tools\commons\collections\ArrayHelper;

/**
 * This trait is an extension of the DataAccessor trait and adds data writing functionality.
 */
trait DataWriter
{
	use DataAccessor;
	
	/**
	 * @param int|string|array<int|string> $key The key/index of the property to set.
	 * @param mixed $value
	 */
	public function set($key, $value): void
	{
		$this->getAll(); // Ensure the load() method is called first.
		
		/** @psalm-suppress PossiblyNullArgument That's why we're calling `getAll()` above. */
		ArrayHelper::set($this->data, $key, $value);
	}
}
