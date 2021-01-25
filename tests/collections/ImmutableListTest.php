<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\ImmutableList;
use js\tools\commons\collections\ImmutableMap;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

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
	
	public function testMap(): void
	{
		$list = new ImmutableList(range(1, 5));
		$newList = $list->map(fn (int $value, int $index) => ($index < 3) ? $value * 2 : $value * 3);
		
		$this->assertSame([2, 4, 6, 12, 15], $newList->get());
		$this->assertSame(range(1, 5), $list->get());
	}
	
	public function testFilter(): void
	{
		$list = new ImmutableList(range(1, 5));
		$newList = $list->filter(fn (int $value, int $index) => $index % 2 === 0);
		
		$this->assertSame([1, 3, 5], $newList->get());
		$this->assertSame(range(1, 5), $list->get());
	}
	
	public function testGroup(): void
	{
		$list = new ImmutableList(range(1, 5));
		$groups = $list->group(fn (int $value, int $index) => $index % 2);
		
		$this->assertInstanceOf(ImmutableMap::class, $groups);
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
		
		$list = new ImmutableList(range(1, 5));
		$list->group(fn (int $value, int $index) => $invalidArrayKey);
	}
	
	public function testFlatten(): void
	{
		$data = [
			1,
			2,
			range(3, 5),
			[
				6,
				[
					range(7, 10),
				],
			],
		];
		$list = new ImmutableList($data);
		$newList = $list->flatten();
		
		$this->assertSame(range(1, 10), $newList->get());
		$this->assertSame($data, $list->get());
	}
	
	public function testSort(): void
	{
		$list1 = new ImmutableList(range(5, 1));
		
		$list2 = $list1->sort(true);
		$this->assertSame(range(1, 5), $list2->get());
		$this->assertSame(range(5, 1), $list1->get());
		
		$list3 = $list2->sort(false);
		$this->assertSame(range(5, 1), $list3->get());
		$this->assertSame(range(1, 5), $list2->get());
		
		$data = ['img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG5.png'];
		
		$list1 = new ImmutableList($data);
		$list2 = $list1->sort(true);
		$this->assertSame(['IMG5.png', 'img1.png', 'img10.png', 'img12.png', 'img2.png'], $list2->get());
		$this->assertSame($data, $list1->get());
		
		$list3 = $list1->sort(true, SORT_NATURAL);
		$this->assertSame(['IMG5.png', 'img1.png', 'img2.png', 'img10.png', 'img12.png'], $list3->get());
		$this->assertSame($data, $list1->get());
		
		$list4 = $list1->sort(true, SORT_NATURAL | SORT_FLAG_CASE);
		$this->assertSame(['img1.png', 'img2.png', 'IMG5.png', 'img10.png', 'img12.png'], $list4->get());
		$this->assertSame($data, $list1->get());
	}
	
	public function testSortManual(): void
	{
		$data = ['img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG5.png'];
		$list1 = new ImmutableList($data);
		
		$list2 = $list1->sortManual(fn (string $a, string $b) => $a <=> $b);
		$this->assertSame(['IMG5.png', 'img1.png', 'img10.png', 'img12.png', 'img2.png'], $list2->get());
		$this->assertSame($data, $list1->get());
		
		$list3 = $list1->sortManual(fn (string $a, string $b) => strnatcmp($a, $b));
		$this->assertSame(['IMG5.png', 'img1.png', 'img2.png', 'img10.png', 'img12.png'], $list3->get());
		$this->assertSame($data, $list1->get());
		
		$list4 = $list1->sortManual(fn (string $a, string $b) => strnatcasecmp($a, $b));
		$this->assertSame(['img1.png', 'img2.png', 'IMG5.png', 'img10.png', 'img12.png'], $list4->get());
		$this->assertSame($data, $list1->get());
	}
}
