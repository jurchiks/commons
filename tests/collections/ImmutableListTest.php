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
}
