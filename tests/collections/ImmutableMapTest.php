<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\ImmutableMap;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImmutableMapTest extends TestCase
{
	public function testArrayAccessUpdateKey(): void
	{
		$this->expectException(RuntimeException::class);
		
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map['bar'] = 4;
	}
	
	public function testArrayAccessSetNewKey(): void
	{
		$this->expectException(RuntimeException::class);
		
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map['qux'] = 4;
	}
	
	public function testArrayAccessUnsetExistingKey(): void
	{
		$this->expectException(RuntimeException::class);
		
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		unset($map['bar']);
	}
	
	public function testArrayAccessUnsetUndefinedKey(): void
	{
		$this->expectException(RuntimeException::class);
		
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		unset($map['bar']);
	}
}
