<?php
namespace js\tools\commons\logging;

class DefaultLogger extends Logger
{
	protected function formatMessage(string $message, int $level)
	{
		return strtoupper(self::getLevelName($level)) . ' ' . $message;
	}
	
	protected function write(string $message, int $level)
	{
		error_log($message);
	}
}
