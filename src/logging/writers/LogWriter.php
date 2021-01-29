<?php
namespace js\tools\commons\logging\writers;

use js\tools\commons\exceptions\LogException;

interface LogWriter
{
	/**
	 * @param string $message The formatted message as received from the log formatter.
	 * @param int $logLevel One of the {@link LogLevel} constants.
	 * @throws LogException If the message failed to be written.
	 */
	public function writeMessage(string $message, int $logLevel): void;
}
