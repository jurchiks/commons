<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\None;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NoneTest extends TestCase
{
	public function testIsEmpty(): void
	{
		$option = new None();
		
		$this->assertTrue($option->isEmpty());
	}
	
	public function testIsFound(): void
	{
		$option = new None();
		
		$this->assertFalse($option->isFound());
	}
	
	public function testGet(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('None does not have a value; consider using getOrElse()');
		
		(new None())->get();
	}
	
	public function testGetOrElse(): void
	{
		$option = new None();
		
		$this->assertSame('default', $option->getOrElse('default'));
	}
}
