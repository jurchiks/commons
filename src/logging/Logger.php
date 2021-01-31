<?php
namespace js\tools\commons\logging;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\formatters\LogFormatter;
use js\tools\commons\logging\writers\LogWriter;

/**
 * @method void debug(string $message) @throws LogException If the message failed to be written.
 * @method void notice(string $message) @throws LogException If the message failed to be written.
 * @method void info(string $message) @throws LogException If the message failed to be written.
 * @method void warning(string $message) @throws LogException If the message failed to be written.
 * @method void error(string $message) @throws LogException If the message failed to be written.
 * @method void critical(string $message) @throws LogException If the message failed to be written.
 * @method void fatal(string $message) @throws LogException If the message failed to be written.
 */
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
	 * @param string $message
	 * @throws LogException If the log level is invalid or message failed to be written.
	 * @see debug()
	 * @see notice()
	 * @see info()
	 * @see warning()
	 * @see error()
	 * @see critical()
	 * @see fatal()
	 */
	public final function log(int $level, string $message): void
	{
		LogLevel::getName($level); // Fail-fast in case of invalid $level, ensuring it won't happen later.
		
		if ($this->formatter)
		{
			$message = $this->formatter->getFormattedMessage($message, $level);
		}
		
		$this->writer->writeMessage($message, $level);
	}
	
	/**
	 * @param string $method The name of the log level.
	 * @param array $arguments An array containing the message to log.
	 * @throws LogException If the log level name is invalid or message failed to be written.
	 * @see log()
	 */
	public final function __call(string $method, array $arguments): void
	{
		$this->log(LogLevel::getByName($method), ...$arguments);
	}
}
