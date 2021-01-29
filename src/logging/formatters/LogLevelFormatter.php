<?php
namespace js\tools\commons\logging\formatters;

use js\tools\commons\logging\LogLevel;

class LogLevelFormatter extends LogFormatter
{
	protected function formatMessage(string $message, int $logLevel): string
	{
		return strtoupper(LogLevel::getName($logLevel)) . ' ' . $message;
	}
}
