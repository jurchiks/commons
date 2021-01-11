<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\ArrayMap;
use js\tools\commons\collections\ImmutableList;
use js\tools\commons\collections\ImmutableMap;
use js\tools\commons\collections\MutableList;
use js\tools\commons\collections\MutableMap;
use PHPUnit\Framework\TestCase;

class ArrayMapTest extends TestCase
{
	public function testToMutable(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = $this->getMockForAbstractClass(ArrayMap::class, [$data]);
		$mutableMap = $map->toMutable();
		
		$this->assertInstanceOf(MutableMap::class, $mutableMap);
		$this->assertSame($data, $mutableMap->get());
	}
	
	public function testToImmutable(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = $this->getMockForAbstractClass(ArrayMap::class, [$data]);
		$immutableMap = $map->toImmutable();
		
		$this->assertInstanceOf(ImmutableMap::class, $immutableMap);
		$this->assertSame($data, $immutableMap->get());
	}
	
	public function testToMutableList(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = $this->getMockForAbstractClass(ArrayMap::class, [$data]);
		$mutableList = $map->toMutableList();
		
		$this->assertInstanceOf(MutableList::class, $mutableList);
		$this->assertSame(array_values($data), $mutableList->get());
	}
	
	public function testToImmutableList(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = $this->getMockForAbstractClass(ArrayMap::class, [$data]);
		$immutableList = $map->toImmutableList();
		
		$this->assertInstanceOf(ImmutableList::class, $immutableList);
		$this->assertSame(array_values($data), $immutableList->get());
	}
	
	public function testReduce(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$list = $this->getMockForAbstractClass(ArrayMap::class, [$data]);
		$reducer = fn (int $value, string $key, string $previous) => $previous . $key . $value;
		
		$this->assertSame('foo1bar2baz3', $list->reduce($reducer, ''));
	}
}
