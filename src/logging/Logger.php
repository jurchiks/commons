<?php
namespace js\tools\commons\logging;

use js\tools\commons\exceptions\LogException;

abstract class Logger
{
	const DEBUG = 1;
	const NOTICE = 2;
	const INFO = 3;
	const WARNING = 4;
	const ERROR = 5;
	const CRITICAL = 6;
	const FATAL = 7;
	
	/**
	 * @param int $level : one of the Logger class constants
	 * @param string $message : the message to log; may contain parameter placeholders in sprintf()-accepted format
	 * @param string[] $parameters : the message parameters
	 */
	public final function log(int $level, string $message, ...$parameters)
	{
		$this->write($this->formatMessage(sprintf(trim($message), ...$parameters)), $level);
	}
	
	protected function formatMessage(string $message)
	{
		return $message;
	}
	
	protected abstract function write(string $message, int $level);
	
	public static final function getLevelName(int $level)
	{
		static $names = [
			self::DEBUG    => 'debug',
			self::NOTICE   => 'notice',
			self::INFO     => 'info',
			self::WARNING  => 'warning',
			self::ERROR    => 'error',
			self::CRITICAL => 'critical',
			self::FATAL    => 'fatal',
		];
		
		if (!isset($names[$level]))
		{
			throw new LogException('Invalid log level: ' . $level);
		}
		
		return $names[$level];
	}
}
