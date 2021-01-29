<?php
namespace js\tools\commons\logging;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\formatters\LogFormatter;
use js\tools\commons\logging\writers\LogWriter;

class Logger
{
	private LogWriter $writer;
	private ?LogFormatter $formatter;
	
	public function __construct(LogWriter $writer, LogFormatter $formatter = null)
	{
		$this->writer = $writer;
		$this->formatter = $formatter;
	}
	
	/**
	 * @param int $level One of the {@link LogLevel} constants.
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
		LogLevel::getName($level); // Fail-fast in case of invalid $level, ensuring it won't happen later.
		
		$message = $this->prepareMessage($message, ...$parameters);
		
		if ($this->formatter)
		{
			$message = $this->formatter->getFormattedMessage($message, $level);
		}
		
		$this->writer->writeMessage($message, $level);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function debug(string $message, ...$parameters): void
	{
		$this->log(LogLevel::DEBUG, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function notice(string $message, ...$parameters): void
	{
		$this->log(LogLevel::NOTICE, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function info(string $message, ...$parameters): void
	{
		$this->log(LogLevel::INFO, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function warning(string $message, ...$parameters): void
	{
		$this->log(LogLevel::WARNING, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function error(string $message, ...$parameters): void
	{
		$this->log(LogLevel::ERROR, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function critical(string $message, ...$parameters): void
	{
		$this->log(LogLevel::CRITICAL, $message, ...$parameters);
	}
	
	/**
	 * @param string $message The message to log; may contain parameter placeholders in sprintf()-accepted format.
	 * @param string[] $parameters The message parameters.
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see log
	 */
	public final function fatal(string $message, ...$parameters): void
	{
		$this->log(LogLevel::FATAL, $message, ...$parameters);
	}
	
	protected function prepareMessage(string $message, ...$parameters): string
	{
		return sprintf(trim($message), ...$parameters);
	}
}
