<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\MutableList;
use js\tools\commons\collections\MutableMap;
use PHPUnit\Framework\TestCase;
use stdClass;

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
		$list->remove(4, 5);
		
		$this->assertSame([1, 2, 3], $list->get());
	}
	
	public function testMap(): void
	{
		$list = new MutableList(range(1, 5));
		$list->map(fn (int $value, int $index) => ($index < 3) ? $value * 2 : $value * 3);
		
		$this->assertSame([2, 4, 6, 12, 15], $list->get());
	}
	
	public function testFilter(): void
	{
		$list = new MutableList(range(1, 5));
		$list->filter(fn (int $value, int $index) => $index % 2 === 0);
		
		$this->assertSame([1, 3, 5], $list->get());
	}
	
	public function testGroup(): void
	{
		$list = new MutableList(range(1, 5));
		$groups = $list->group(fn (int $value, int $index) => $index % 2);
		
		$this->assertInstanceOf(MutableMap::class, $groups);
		$this->assertSame(
			[
				0 => [1, 3, 5],
				1 => [2, 4],
			],
			$groups->get()
		);
		$this->assertSame(range(1, 5), $list->get());
	}
	
	public function invalidArrayKeysDataset(): iterable
	{
		yield [null];
		yield [new stdClass()];
		yield [[]];
	}
	
	/** @dataProvider invalidArrayKeysDataset */
	public function testGroupInvalidKey($invalidArrayKey): void
	{
		$this->expectException(InvalidArgumentException::class);
		
		$list = new MutableList(range(1, 5));
		$list->group(fn (int $value, int $index) => $invalidArrayKey);
	}
	
	public function testFlatten(): void
	{
		$list = new MutableList(
			[
				1,
				2,
				range(3, 5),
				[
					6,
					[
						range(7, 10),
					],
				],
			]
		);
		$list->flatten();
		
		$this->assertSame(range(1, 10), $list->get());
	}
	
	public function testSort(): void
	{
		$list = new MutableList(range(5, 1));
		
		$list->sort(true);
		$this->assertSame(range(1, 5), $list->get());
		
		$list->sort(false);
		$this->assertSame(range(5, 1), $list->get());
		
		$list = new MutableList(['img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG5.png']);
		
		$list->sort(true);
		$this->assertSame(['IMG5.png', 'img1.png', 'img10.png', 'img12.png', 'img2.png'], $list->get());
		
		$list->sort(true, SORT_NATURAL);
		$this->assertSame(['IMG5.png', 'img1.png', 'img2.png', 'img10.png', 'img12.png'], $list->get());
		
		$list->sort(true, SORT_NATURAL | SORT_FLAG_CASE);
		$this->assertSame(['img1.png', 'img2.png', 'IMG5.png', 'img10.png', 'img12.png'], $list->get());
	}
	
	public function testSortManual(): void
	{
		$list = new MutableList(['img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG5.png']);
		
		$list->sortManual(fn (string $a, string $b) => $a <=> $b);
		$this->assertSame(['IMG5.png', 'img1.png', 'img10.png', 'img12.png', 'img2.png'], $list->get());
		
		$list->sortManual(fn (string $a, string $b) => strnatcmp($a, $b));
		$this->assertSame(['IMG5.png', 'img1.png', 'img2.png', 'img10.png', 'img12.png'], $list->get());
		
		$list->sortManual(fn (string $a, string $b) => strnatcasecmp($a, $b));
		$this->assertSame(['img1.png', 'img2.png', 'IMG5.png', 'img10.png', 'img12.png'], $list->get());
	}
}
