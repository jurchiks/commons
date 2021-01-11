<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\MutableMap;
use PHPUnit\Framework\TestCase;

class MutableMapTest extends TestCase
{
	public function testArrayAccessUpdateKey(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map['bar'] = 4;
		
		$this->assertSame(['foo' => 1, 'bar' => 4, 'baz' => 3], $map->get());
	}
	
	public function testArrayAccessSetNewKey(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map['qux'] = 4;
		
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4], $map->get());
	}
	
	public function testArrayAccessUnsetExistingKey(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		unset($map['bar']);
		
		$this->assertSame(['foo' => 1, 'baz' => 3], $map->get());
	}
	
	public function testArrayAccessUnsetUndefinedKey(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		unset($map['bar']);
		
		$this->assertSame(['foo' => 1, 'baz' => 3], $map->get());
	}
	
	public function testSet(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->set('qux', 4);
		
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4], $map->get());
	}
	
	public function testUnset(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->unset('bar');
		
		$this->assertSame(['foo' => 1, 'baz' => 3], $map->get());
	}
	
	public function testRemove(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->remove(2);
		
		$this->assertSame(['foo' => 1, 'baz' => 3], $map->get());
	}
}
