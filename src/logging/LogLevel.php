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
	
	private const NAMES = [
		self::DEBUG    => 'debug',
		self::NOTICE   => 'notice',
		self::INFO     => 'info',
		self::WARNING  => 'warning',
		self::ERROR    => 'error',
		self::CRITICAL => 'critical',
		self::FATAL    => 'fatal',
	];
	
	/**
	 * @param int $level One of the {@link LogLevel} constants.
	 * @return string The name of the log level.
	 * @throws LogException If the level is invalid.
	 * @see getByName() The inverse of this method.
	 */
	public static function getName(int $level): string
	{
		if (isset(self::NAMES[$level]))
		{
			return self::NAMES[$level];
		}
		
		throw new LogException('Invalid log level: ' . $level);
	}
	
	/**
	 * @param string $name The name of the log level.
	 * @return int The found log level.
	 * @throws LogException If the name is invalid.
	 * @see getName() The inverse of this method.
	 */
	public static function getByName(string $name): int
	{
		$logLevel = array_search($name, self::NAMES, true);
		
		if ($logLevel === false)
		{
			throw new LogException('Invalid log level name: ' . $name);
		}
		
		return $logLevel;
	}
}
