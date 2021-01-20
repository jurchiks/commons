<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\Some;
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
	public function testIsEmpty(): void
	{
		$option = new Some('value');
		
		$this->assertFalse($option->isEmpty());
	}
	
	public function testIsFound(): void
	{
		$option = new Some('value');
		
		$this->assertTrue($option->isFound());
	}
	
	public function testGet(): void
	{
		$option = new Some('value');
		
		$this->assertSame('value', $option->get());
	}
	
	public function testGetOrElse(): void
	{
		$option = new Some('value');
		
		$this->assertSame('value', $option->getOrElse('default'));
	}
}
