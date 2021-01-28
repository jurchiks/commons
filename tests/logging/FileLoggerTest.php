<?php
namespace js\tools\commons\tests\logging;

use js\tools\commons\logging\FileLogger;
use js\tools\commons\logging\LogLevel;
use PHPUnit\Framework\TestCase;

class FileLoggerTest extends TestCase
{
	public static function tearDownAfterClass(): void
	{
		foreach (glob(__DIR__ . '/*.log') as $logFile)
		{
			unlink($logFile);
		}
	}
	
	public function testWriteToFile(): void
	{
		$logger = new FileLogger(__DIR__);
		$logger->info('foo');
		
		$logFile = __DIR__ . '/info.log';
		$this->assertFileExists($logFile);
		$this->assertStringEqualsFile(
			$logFile,
			'[' . date('Y-m-d H:i:s') . '] ' . strtoupper(LogLevel::getName(LogLevel::INFO)) . ' foo' . PHP_EOL
		);
	}
}
