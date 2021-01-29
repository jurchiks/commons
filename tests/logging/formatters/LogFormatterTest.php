<?php
namespace js\tools\commons\tests\logging\formatters;

use js\tools\commons\logging\formatters\LogFormatter;
use js\tools\commons\logging\LogLevel;
use PHPUnit\Framework\TestCase;

class LogFormatterTest extends TestCase
{
	public function testSingleLevelFormatter(): void
	{
		$message = 'Message';
		
		$formatter = $this->getMockForAbstractClass(LogFormatter::class);
		$formatter->method('formatMessage')
			->with($message, LogLevel::INFO)
			->willReturnArgument(0);
		
		$this->assertSame($message, $formatter->getFormattedMessage($message, LogLevel::INFO));
	}
	
	public function testNestedFormatters(): void
	{
		$firstFormatter = new class extends LogFormatter
		{
			protected function formatMessage(string $message, int $logLevel): string
			{
				return strtolower($message);
			}
		};
		$secondFormatter = new class ($firstFormatter) extends LogFormatter
		{
			protected function formatMessage(string $message, int $logLevel): string
			{
				return strtoupper(LogLevel::getName($logLevel)) . ' ' . $message;
			}
		};
		$thirdFormatter = new class ($secondFormatter) extends LogFormatter
		{
			protected function formatMessage(string $message, int $logLevel): string
			{
				return '[' . date('Y-m-d') . '] ' . $message;
			}
		};
		
		$this->assertSame(
			'[' . date('Y-m-d') . '] INFO lowercase',
			$thirdFormatter->getFormattedMessage('LOWERCASE', LogLevel::INFO)
		);
	}
}
