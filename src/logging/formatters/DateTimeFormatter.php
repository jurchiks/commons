<?php
namespace js\tools\commons\logging\formatters;

class DateTimeFormatter extends LogFormatter
{
	private string $dateTimeFormat;
	
	public function __construct(?LogFormatter $previousFormatter = null, string $dateTimeFormat = 'Y-m-d H:i:s.u')
	{
		parent::__construct($previousFormatter);
		$this->dateTimeFormat = $dateTimeFormat;
	}
	
	protected function formatMessage(string $message, int $logLevel): string
	{
		return '[' . date($this->dateTimeFormat) . '] ' . $message;
	}
}
