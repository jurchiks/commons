<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\ImmutableList;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImmutableListTest extends TestCase
{
	public function testArrayAccessUpdateIndex(): void
	{
		$this->expectException(RuntimeException::class);
		
		$list = new ImmutableList(range(1, 5));
		$list[2] = 5;
	}
	
	public function testArrayAccessSetNewIndex(): void
	{
		$this->expectException(RuntimeException::class);
		
		$list = new ImmutableList(range(1, 5));
		$list[5] = 6;
	}
	
	public function testArrayAccessUnsetExistingIndex(): void
	{
		$this->expectException(RuntimeException::class);
		
		$list = new ImmutableList(range(1, 5));
		unset($list[2]);
	}
	
	public function testArrayAccessUnsetUndefinedIndex(): void
	{
		$this->expectException(RuntimeException::class);
		
		$list = new ImmutableList(range(1, 5));
		unset($list[5]);
	}
	
	public function testAppend(): void
	{
		$list = new ImmutableList(range(1, 5));
		$newList = $list->append(...range(6, 10));
		
		$this->assertSame(range(1, 10), $newList->get());
		$this->assertSame(range(1, 5), $list->get());
	}
	
	public function testPrepend(): void
	{
		$list = new ImmutableList(range(1, 5));
		$newList = $list->prepend(...range(6, 10));
		
		$this->assertSame([...range(6, 10), ...range(1, 5)], $newList->get());
		$this->assertSame(range(1, 5), $list->get());
	}
	
	public function testRemove(): void
	{
		$list = new ImmutableList(range(1, 5));
		$newList = $list->remove(4);
		
		$this->assertSame([1, 2, 3, 5], $newList->get());
		$this->assertSame(range(1, 5), $list->get());
	}
}
