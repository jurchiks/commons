<?php
namespace js\tools\commons\logging\formatters;

class DateTimeFormatter extends LogFormatter
{
	protected function formatMessage(string $message, int $logLevel): string
	{
		return '[' . date('Y-m-d H:i:s.u') . '] ' . $message;
	}
}
