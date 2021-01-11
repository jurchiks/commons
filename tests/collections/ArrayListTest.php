<?php
namespace js\tools\commons\tests\collections;

use ArrayIterator;
use js\tools\commons\collections\ArrayList;
use js\tools\commons\collections\ImmutableList;
use js\tools\commons\collections\ImmutableMap;
use js\tools\commons\collections\MutableList;
use js\tools\commons\collections\MutableMap;
use PHPUnit\Framework\TestCase;

class ArrayListTest extends TestCase
{
	public function testConstructorWithArray(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$list = $this->getMockForAbstractClass(ArrayList::class, [$data]);
		
		$this->assertSame(array_values($data), $list->get());
	}
	
	public function testConstructorWithTraversable(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$iterator = new ArrayIterator($data);
		$list = $this->getMockForAbstractClass(ArrayList::class, [$iterator]);
		
		$this->assertSame(array_values($data), $list->get());
	}
	
	public function testToMutable(): void
	{
		$data = range(1, 5);
		$list = $this->getMockForAbstractClass(ArrayList::class, [$data]);
		$mutableList = $list->toMutable();
		
		$this->assertInstanceOf(MutableList::class, $mutableList);
		$this->assertSame($data, $mutableList->get());
	}
	
	public function testToImmutable(): void
	{
		$data = range(1, 5);
		$list = $this->getMockForAbstractClass(ArrayList::class, [$data]);
		$immutableList = $list->toImmutable();
		
		$this->assertInstanceOf(ImmutableList::class, $immutableList);
		$this->assertSame($data, $immutableList->get());
	}
	
	public function testToMutableMap(): void
	{
		$data = range(1, 5);
		$list = $this->getMockForAbstractClass(ArrayList::class, [$data]);
		$mutableMap = $list->toMutableMap();
		
		$this->assertInstanceOf(MutableMap::class, $mutableMap);
		$this->assertSame($data, $mutableMap->get());
	}
	
	public function testToImmutableMap(): void
	{
		$data = range(1, 5);
		$list = $this->getMockForAbstractClass(ArrayList::class, [$data]);
		$immutableMap = $list->toImmutableMap();
		
		$this->assertInstanceOf(ImmutableMap::class, $immutableMap);
		$this->assertSame($data, $immutableMap->get());
	}
	
	public function testReduce(): void
	{
		$data = range(1, 5);
		$list = $this->getMockForAbstractClass(ArrayList::class, [$data]);
		$reducer = fn (int $value, string $previous) => $previous . $value;
	
		$this->assertSame('12345', $list->reduce($reducer, ''));
	}
}
