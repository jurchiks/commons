<?php
namespace js\tools\commons\logging;

use js\tools\commons\exceptions\LogException;

class LogLevel
{
	public const DEBUG = 1;
	public const NOTICE = 2;
	public const INFO = 3;
	public const WARNING = 4;
	public const ERROR = 5;
	public const CRITICAL = 6;
	public const FATAL = 7;
	
	/**
	 * @param int $level One of the {@link LogLevel} constants.
	 * @return string The name of the log level.
	 * @throws LogException If the level is invalid.
	 */
	public static function getName(int $level): string
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
		
		if (isset($names[$level]))
		{
			return $names[$level];
		}
		
		throw new LogException('Invalid log level: ' . $level);
	}
}
