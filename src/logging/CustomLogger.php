<?php
namespace js\tools\commons\logging;

class CustomLogger extends Logger
{
	/** @var callable */
	private $writer;
	
	/**
	 * @param callable $writer The function to call on writes; signature: `(string $message, int $level): void`.
	 */
	public function __construct(callable $writer)
	{
		$this->writer = $writer;
	}
	
	protected function write(string $message, int $level): void
	{
		call_user_func($this->writer, $message, $level);
	}
}
