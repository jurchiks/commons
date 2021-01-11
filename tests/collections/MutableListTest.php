<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\MutableList;
use PHPUnit\Framework\TestCase;

class MutableListTest extends TestCase
{
	public function testArrayAccessUpdateIndex(): void
	{
		$list = new MutableList(range(1, 5));
		$list[2] = 6;
		
		$this->assertSame([1, 2, 6, 4, 5], $list->get());
	}
	
	public function testArrayAccessSetNewIndex(): void
	{
		$this->expectException(InvalidArgumentException::class);
		
		$list = new MutableList(range(1, 5));
		$list[5] = 6;
	}
	
	public function testArrayAccessUnsetExistingIndex(): void
	{
		$list = new MutableList(range(1, 5));
		unset($list[2]);
		
		$this->assertSame([1, 2, 4, 5], $list->get());
	}
	
	public function testArrayAccessUnsetUndefinedIndex(): void
	{
		$data = range(1, 5);
		$list = new MutableList($data);
		unset($list[5]);
		
		$this->assertSame($data, $list->get());
	}
	
	public function testAppend(): void
	{
		$list = new MutableList(range(1, 5));
		$list->append(...range(6, 10));
		
		$this->assertSame(range(1, 10), $list->get());
	}
	
	public function testPrepend(): void
	{
		$list = new MutableList(range(1, 5));
		$list->prepend(...range(6, 10));
		
		$this->assertSame([...range(6, 10), ...range(1, 5)], $list->get());
	}
	
	public function testRemove(): void
	{
		$list = new MutableList(range(1, 5));
		$list->remove(4);
		
		$this->assertSame([1, 2, 3, 5], $list->get());
	}
}
