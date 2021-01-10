<?php
namespace js\tools\commons\tests\collections;

use ArrayIterator;
use js\tools\commons\collections\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class CollectionTest extends TestCase
{
	public function testConstructorWithArray(): void
	{
		$data = ['foo' => 1];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$this->assertSame($data, $collection->get());
	}
	
	public function testConstructorWithTraversable(): void
	{
		$data = ['foo' => 1];
		$iterator = new ArrayIterator($data);
		$collection = $this->getMockForAbstractClass(Collection::class, [$iterator]);
		
		$this->assertSame($data, $collection->get());
	}
	
	public function invalidConstructorParametersDataset(): iterable
	{
		yield [null];
		yield [true];
		yield [1];
		yield [1.5];
		yield ['string'];
		yield [new stdClass()];
	}
	
	/** @dataProvider invalidConstructorParametersDataset */
	public function testConstructorWithNonIterable($value): void
	{
		$this->expectException(TypeError::class);
		$this->getMockForAbstractClass(Collection::class, [$value]);
	}
}
