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
	 * @param int $level One of the Logger class constants.
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see debug()
	 * @see notice()
	 * @see info()
	 * @see warning()
	 * @see error()
	 * @see critical()
	 * @see fatal()
	 */
	public final function log(int $level, string $message, ...$parameters): void
	{
		$message = $this->prepareMessage($message, ...$parameters);
		$message = $this->formatMessage($message, $level);
		
		$this->write($message, $level);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function debug(string $message, ...$parameters): void
	{
		$this->log(self::DEBUG, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function notice(string $message, ...$parameters): void
	{
		$this->log(self::NOTICE, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function info(string $message, ...$parameters): void
	{
		$this->log(self::INFO, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function warning(string $message, ...$parameters): void
	{
		$this->log(self::WARNING, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function error(string $message, ...$parameters): void
	{
		$this->log(self::ERROR, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function critical(string $message, ...$parameters): void
	{
		$this->log(self::CRITICAL, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function fatal(string $message, ...$parameters): void
	{
		$this->log(self::FATAL, $message, ...$parameters);
	}
	
	protected function prepareMessage(string $message, ...$parameters): string
	{
		return sprintf(trim($message), ...$parameters);
	}
	
	/**
	 * @param string $message The source message received from the logging methods.
	 * @param int $level One of the {@link Logger} constants.
	 * @return string The formatted message.
	 * @throws LogException If the log level is invalid.
	 * @noinspection PhpDocRedundantThrowsInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function formatMessage(string $message, int $level): string
	{
		return $message;
	}
	
	/**
	 * @param string $message The formatted message received from {@link formatMessage()}.
	 * @param int $level One of the {@link Logger} constants.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 */
	protected abstract function write(string $message, int $level): void;
	
	/**
	 * @param int $level One of the {@link Logger} constants.
	 * @return string The name of the log level.
	 * @throws LogException If the level is invalid.
	 */
	public static final function getLevelName(int $level): string
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
