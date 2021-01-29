<?php
namespace js\tools\commons\tests\logging\formatters;

use js\tools\commons\logging\formatters\DateTimeFormatter;
use js\tools\commons\logging\LogLevel;
use PHPUnit\Framework\TestCase;

class DateTimeFormatterTest extends TestCase
{
	public function testFormatMessage(): void
	{
		$formatter = new DateTimeFormatter();
		
		$this->assertStringMatchesFormat(
			'[%d-%d-%d %d:%d:%d.%d] foo',
			$formatter->getFormattedMessage('foo', LogLevel::INFO)
		);
	}
}
