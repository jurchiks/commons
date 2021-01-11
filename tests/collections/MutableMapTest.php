<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\MutableMap;
use PHPUnit\Framework\TestCase;
use stdClass;

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
	
	public function testMap(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->map(
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
		
		$this->assertSame(['foo' => 3, 'bar' => 4, 'baz' => 9], $map->get());
	}
	
	public function testFilterWithKeys(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->filter(fn (int $value, string $key) => (($key === 'bar') || ($value === 3)), true);
		
		$this->assertSame(['bar' => 2, 'baz' => 3], $map->get());
	}
	
	public function testFilterWithoutKeys(): void
	{
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->filter(fn (int $value, string $key) => (($key === 'bar') || ($value === 3)), false);
		
		$this->assertSame([2, 3], $map->get());
	}
	
	public function testGroupWithKeys(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new MutableMap($data);
		$map->group(fn (int $value, string $key) => $value % 2, true);
		
		$this->assertInstanceOf(MutableMap::class, $map);
		$this->assertSame(
			[
				1 => ['foo' => 1, 'baz' => 3],
				0 => ['bar' => 2],
			],
			$map->get()
		);
	}
	
	public function testGroupWithoutKeys(): void
	{
		$data = ['foo' => 1, 'bar' => 2, 'baz' => 3];
		$map = new MutableMap($data);
		$map->group(fn (int $value, string $key) => $value % 2, false);
		
		$this->assertInstanceOf(MutableMap::class, $map);
		$this->assertSame(
			[
				1 => [1, 3],
				0 => [2],
			],
			$map->get()
		);
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
		
		$map = new MutableMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);
		$map->group(fn (int $value, string $key) => $invalidArrayKey);
	}
	
	public function testFlattenWithKeys(): void
	{
		$map = new MutableMap(
			[
				'first'  => 1,
				'second' => 2,
				'third'  => 3,
				[
					'fourth' => 4,
					[
						'fifth' => 5,
					],
				],
			]
		);
		$map->flatten(true);
		
		$this->assertSame(
			[
				'first'  => 1,
				'second' => 2,
				'third'  => 3,
				'fourth' => 4,
				'fifth'  => 5,
			],
			$map->get()
		);
	}
	
	public function testFlattenWithoutKeys(): void
	{
		$map = new MutableMap(
			[
				'first'  => 1,
				'second' => 2,
				'third'  => 3,
				[
					'fourth' => 4,
					[
						'fifth' => 5,
					],
				],
			]
		);
		$map->flatten(false);
		
		$this->assertSame([1, 2, 3, 4, 5], $map->get());
	}
}
