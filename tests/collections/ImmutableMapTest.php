<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\ImmutableMap;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

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
	
	public function testSet(): void
	{
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$newMap = $map->set('qux', 4);
		
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4], $newMap->get());
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3], $map->get());
	}
	
	public function testUnset(): void
	{
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$newMap = $map->unset('bar');
		
		$this->assertSame(['foo' => 1, 'baz' => 3], $newMap->get());
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3], $map->get());
	}
	
	public function testRemove(): void
	{
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$newMap = $map->remove(2);
		
		$this->assertSame(['foo' => 1, 'baz' => 3], $newMap->get());
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3], $map->get());
	}
	
	public function testMap(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new ImmutableMap($data);
		$newMap = $map->map(
			function (int $value, string $key)
			{
				if ($key === 'bar')
				{
					return $value * 2;
				}
				else
				{
					return $value * 3;
				}
			}
		);
		
		$this->assertSame(['foo' => 3, 'bar' => 4, 'baz' => 9], $newMap->get());
		$this->assertSame($data, $map->get());
	}
	
	public function testFilterWithKeys(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new ImmutableMap($data);
		$newMap = $map->filter(fn (int $value, string $key) => (($key === 'bar') || ($value === 3)), true);
		
		$this->assertSame(['bar' => 2, 'baz' => 3], $newMap->get());
		$this->assertSame($data, $map->get());
	}
	
	public function testFilterWithoutKeys(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new ImmutableMap($data);
		$newMap = $map->filter(fn (int $value, string $key) => (($key === 'bar') || ($value === 3)), false);
		
		$this->assertSame([2, 3], $newMap->get());
		$this->assertSame($data, $map->get());
	}
	
	public function testGroupWithKeys(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new ImmutableMap($data);
		$groups = $map->group(fn (int $value, string $key) => $value % 2, true);
		
		$this->assertInstanceOf(ImmutableMap::class, $groups);
		$this->assertSame(
			[
				1 => ['foo' => 1, 'baz' => 3],
				0 => ['bar' => 2],
			],
			$groups->get()
		);
		$this->assertSame($data, $map->get());
	}
	
	public function testGroupWithoutKeys(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new ImmutableMap($data);
		$groups = $map->group(fn (int $value, string $key) => $value % 2, false);
		
		$this->assertInstanceOf(ImmutableMap::class, $groups);
		$this->assertSame(
			[
				1 => [1, 3],
				0 => [2],
			],
			$groups->get()
		);
		$this->assertSame($data, $map->get());
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
		
		$map = new ImmutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->group(fn (int $value, string $key) => $invalidArrayKey);
	}
	
	public function testFlattenWithKeys(): void
	{
		$data = [
			'first'  => 1,
			'second' => 2,
			'third'  => 3,
			[
				'fourth' => 4,
				[
					'fifth' => 5,
				],
			],
		];
		$map = new ImmutableMap($data);
		$newMap = $map->flatten(true);
		
		$this->assertSame(
			[
				'first'  => 1,
				'second' => 2,
				'third'  => 3,
				'fourth' => 4,
				'fifth'  => 5,
			],
			$newMap->get()
		);
		$this->assertSame($data, $map->get());
	}
	
	public function testFlattenWithoutKeys(): void
	{
		$data = [
			'first'  => 1,
			'second' => 2,
			'third'  => 3,
			[
				'fourth' => 4,
				[
					'fifth' => 5,
				],
			],
		];
		$map = new ImmutableMap($data);
		$newMap = $map->flatten(false);
		
		$this->assertSame([1, 2, 3, 4, 5], $newMap->get());
		$this->assertSame($data, $map->get());
	}
	
	public function testSort(): void
	{
		$data = ['bar' => 2, 'foo' => 1, 'baz' => 3];
		$map1 = new ImmutableMap($data);
		
		$map2 = $map1->sort(true);
		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3], $map2->get());
		$this->assertSame($data, $map1->get());
		
		$map3 = $map1->sort(false);
		$this->assertSame(['baz' => 3, 'bar' => 2, 'foo' => 1], $map3->get());
		$this->assertSame($data, $map1->get());
		
		$map4 = $map1->sort(true, SORT_REGULAR, true);
		$this->assertSame(['bar' => 2, 'baz' => 3, 'foo' => 1], $map4->get());
		$this->assertSame($data, $map1->get());
		
		$map5 = $map1->sort(false, SORT_REGULAR, true, false);
		$this->assertSame([1, 3, 2], $map5->get());
		$this->assertSame($data, $map1->get());
		
		$map6 = $map1->sort(true, SORT_REGULAR, false, false);
		$this->assertSame([1, 2, 3], $map6->get());
		$this->assertSame($data, $map1->get());
		
		$data = ['bar' => 'img12.png', 'foo' => 'IMG10.png', 'baz' => 'img5.png'];
		$map1 = new ImmutableMap($data);
		
		$map2 = $map1->sort(true);
		$this->assertSame(['foo' => 'IMG10.png', 'bar' => 'img12.png', 'baz' => 'img5.png'], $map2->get());
		$this->assertSame($data, $map1->get());
		
		$map3 = $map1->sort(true, SORT_NATURAL);
		$this->assertSame(['foo' => 'IMG10.png', 'baz' => 'img5.png', 'bar' => 'img12.png'], $map3->get());
		$this->assertSame($data, $map1->get());
		
		$map4 = $map1->sort(false, SORT_NATURAL | SORT_FLAG_CASE);
		$this->assertSame(['bar' => 'img12.png', 'foo' => 'IMG10.png', 'baz' => 'img5.png'], $map4->get());
		$this->assertSame($data, $map1->get());
	}
	
	public function testSortManual(): void
	{
		$data = ['bar' => 'img12.png', 'foo' => 'IMG10.png', 'baz' => 'img5.png'];
		$map1 = new ImmutableMap($data);
		
		$map2 = $map1->sortManual(fn (string $a, string $b) => $a <=> $b);
		$this->assertSame(['foo' => 'IMG10.png', 'bar' => 'img12.png', 'baz' => 'img5.png'], $map2->get());
		$this->assertSame($data, $map1->get());
		
		$map3 = $map1->sortManual(fn (string $a, string $b) => strnatcmp($a, $b));
		$this->assertSame(['foo' => 'IMG10.png', 'baz' => 'img5.png', 'bar' => 'img12.png'], $map3->get());
		$this->assertSame($data, $map1->get());
		
		$map4 = $map1->sortManual(fn (string $a, string $b) => strnatcasecmp($a, $b));
		$this->assertSame(['baz' => 'img5.png', 'foo' => 'IMG10.png', 'bar' => 'img12.png'], $map4->get());
		$this->assertSame($data, $map1->get());
		
		$map5 = $map1->sortManual(fn (string $a, string $b) => $a <=> $b, true, true);
		$this->assertSame(['bar' => 'img12.png', 'baz' => 'img5.png', 'foo' => 'IMG10.png'], $map5->get());
		$this->assertSame($data, $map1->get());
		
		$map6 = $map1->sortManual(fn (string $a, string $b) => $a <=> $b, true, false);
		$this->assertSame(['img12.png', 'img5.png', 'IMG10.png'], $map6->get());
		$this->assertSame($data, $map1->get());
		
		$map7 = $map1->sortManual(fn (string $a, string $b) => $a <=> $b, false, false);
		$this->assertSame(['IMG10.png', 'img12.png', 'img5.png'], $map7->get());
		$this->assertSame($data, $map1->get());
	}
}
