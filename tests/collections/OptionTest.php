<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\Option;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
	public function foundDataset(): iterable
	{
		yield [new Option('whatever', true), true];
		yield [new Option('whatever', false), false];
	}
	
	/** @dataProvider foundDataset */
	public function testIsFound(Option $option, bool $shouldBeFound): void
	{
		$this->assertSame($shouldBeFound, $option->isFound());
	}
	
	public function testGetValid(): void
	{
		$option = new Option('whatever', true);
		
		$this->assertSame('whatever', $option->get());
	}
	
	public function testGetInvalid(): void
	{
		$option = new Option('whatever', false);
		
		$this->expectException(InvalidArgumentException::class);
		$option->get();
	}
	
	public function testGetOrElseValid(): void
	{
		$option = new Option('whatever', true);
		
		$this->assertSame('whatever', $option->getOrElse('fallback'));
	}
	
	public function testGetOrElseInvalid(): void
	{
		$option = new Option(null, false);
		
		$this->assertSame('fallback', $option->getOrElse('fallback'));
	}
}
