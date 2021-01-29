<?php
namespace js\tools\commons\logging\formatters;

abstract class LogFormatter
{
	protected ?LogFormatter $previousFormatter;
	
	/**
	 * Create a new formatter, optionally nesting other formatters inside it.
	 * The previous formatters should always be respected by calling their formatting either before or after yours.
	 *
	 * @param LogFormatter|null $previousFormatter
	 */
	public function __construct(?LogFormatter $previousFormatter = null)
	{
		$this->previousFormatter = $previousFormatter;
	}
	
	/**
	 * @param string $message The message as received from the logging methods or passed on from parent formatters.
	 * @param int $logLevel One of the {@link LogLevel} constants.
	 * @return string The formatted message.
	 */
	public final function getFormattedMessage(string $message, int $logLevel): string
	{
		if ($this->previousFormatter)
		{
			$message = $this->previousFormatter->getFormattedMessage($message, $logLevel);
		}
		
		return $this->formatMessage($message, $logLevel);
	}
	
	protected abstract function formatMessage(string $message, int $logLevel): string;
}
