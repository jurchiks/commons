<?php
namespace js\tools\commons\logging;

class CustomLogger extends Logger
{
	/** @var callable */
	private $callback;
	
	/**
	 * @param callable $callback : the function to call on writes;
	 * this function will receive two parameters - string $message and int $level
	 */
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}
	
	protected function write(string $message, int $level)
	{
		call_user_func($this->callback, $message, $level);
	}
}
