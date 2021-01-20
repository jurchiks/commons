<?php
namespace js\tools\commons\tests\config;

use js\tools\commons\config\Config;
use js\tools\commons\exceptions\ConfigException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
	public function testLoadFromFileInvalidFilePath(): void
	{
		$path = __DIR__ . '/no_such_file.php';
		
		$this->expectException(ConfigException::class);
		$this->expectExceptionMessage('Config file is not available: ' . $path);
		
		Config::loadFromFile($path);
	}
	
	public function testLoadFromFilePhpValid(): void
	{
		$config = Config::loadFromFile(__DIR__ . '/valid_php.php');
		
		$this->assertInstanceOf(Config::class, $config);
		$this->assertSame(
			[
				'foo' => 1,
				'bar' => [
					'baz' => 'qux',
				],
			],
			$config->getAll()
		);
	}
	
	public function testLoadFromFilePhpInvalid(): void
	{
		$this->expectException(ConfigException::class);
		$this->expectExceptionMessage('Config file must return an array');
		
		Config::loadFromFile(__DIR__ . '/invalid_php.php');
	}
	
	public function testLoadFromFileJsonValid(): void
	{
		$config = Config::loadFromFile(__DIR__ . '/valid_json.json', true);
		
		$this->assertInstanceOf(Config::class, $config);
		$this->assertSame(
			[
				'foo' => 1,
				'bar' => [
					'baz' => 'qux',
				],
			],
			$config->getAll()
		);
	}
	
	public function testLoadFromFileJsonInvalid(): void
	{
		$this->expectException(ConfigException::class);
		$this->expectExceptionMessage('Config file must return an array');
		
		Config::loadFromFile(__DIR__ . '/invalid_json.json', true);
	}
	
	public function testLoadFromJsonValid(): void
	{
		$data = [
			'foo' => 1,
			'bar' => [
				'baz' => 'qux',
			],
		];
		$config = Config::loadFromJson(json_encode($data));
		
		$this->assertInstanceOf(Config::class, $config);
		$this->assertSame(
			$data,
			$config->getAll()
		);
	}
	
	public function testLoadFromJsonInvalid(): void
	{
		$this->expectException(ConfigException::class);
		$this->expectExceptionMessage('Json data must contain an array');
		
		Config::loadFromJson('"not an array"');
	}
	
	public function testLoadFromArray(): void
	{
		$data = [
			'foo' => 1,
			'bar' => [
				'baz' => 'qux',
			],
		];
		$config = Config::loadFromArray($data);
		
		$this->assertInstanceOf(Config::class, $config);
		$this->assertSame(
			$data,
			$config->getAll()
		);
	}
}
