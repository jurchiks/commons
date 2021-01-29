<?php
namespace js\tools\commons\logging\writers;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\LogLevel;

class FileWriter implements LogWriter
{
	private string $logDirectory;
	
	/**
	 * @param string $logDirectory The directory in which to put the log files.
	 * @param int $permissions The folder permissions to use in case the log directory does not exist and has to be
	 *     created.
	 * @throws LogException If something is wrong with the log directory.
	 */
	public function __construct(string $logDirectory, int $permissions = 0666)
	{
		if (!is_dir($logDirectory) && !mkdir($logDirectory, $permissions, true))
		{
			throw new LogException(
				'Log directory does not exist and cannot be made, check permissions: ' . $logDirectory
			);
		}
		
		if (!is_writable($logDirectory))
		{
			throw new LogException('Log directory is not writable, check permissions: ' . $logDirectory);
		}
		
		$this->logDirectory = rtrim($logDirectory, '\\/');
	}
	
	public function writeMessage(string $message, int $logLevel): void
	{
		$message .= PHP_EOL; // Add a line break after each message, otherwise the logs will become unreadable.
		$path = $this->logDirectory . '/' . LogLevel::getName($logLevel) . '.log';
		$success = file_put_contents($path, $message, FILE_APPEND | LOCK_EX);
		
		if ($success === false)
		{
			throw new LogException('Failed to log a message to ' . $path . ', check permissions');
		}
	}
}
