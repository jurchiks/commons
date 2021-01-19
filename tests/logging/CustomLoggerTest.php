<?php
namespace js\tools\commons\tests\logging;

use js\tools\commons\logging\CustomLogger;
use PHPUnit\Framework\TestCase;

class CustomLoggerTest extends TestCase
{
	public function testCustomLogger(): void
	{
		$this->expectOutputString('foo');
		
		$logHandler = function (string $message)
		{
			echo $message;
		};
		$logger = new CustomLogger($logHandler);
		$logger->info('foo');
	}
}
