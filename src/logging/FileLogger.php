<?php
namespace js\tools\commons\logging;

use js\tools\commons\exceptions\LogException;

class FileLogger extends Logger
{
	private $logDirectory;
	
	/**
	 * @param string $logDirectory : the directory in which to put the log files
	 * @param int $permissions : the folder permissions to use in case the log directory does not exist and has to be
	 *     created
	 * @throws LogException if something is wrong with the log directory
	 */
	public function __construct(string $logDirectory, int $permissions = 0666)
	{
		if (!is_dir($logDirectory) && !mkdir($logDirectory, $permissions, true))
		{
			throw new LogException('Log directory does not exist and cannot be made: ' . $logDirectory);
		}
		
		if (!is_writable($logDirectory))
		{
			throw new LogException('Log directory is not writable, check permissions: ' . $logDirectory);
		}
		
		$this->logDirectory = rtrim($logDirectory, '\\/');
	}
	
	protected function formatMessage(string $message)
	{
		return '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
	}
	
	protected function write(string $message, int $level)
	{
		$path = $this->logDirectory . '/' . self::getLevelName($level) . '.log';
		$success = file_put_contents($path, $message, FILE_APPEND | LOCK_EX);
		
		if ($success === false)
		{
			throw new LogException('Failed to log a message to ' . $path);
		}
	}
}
