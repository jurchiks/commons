<?php
namespace js\tools\commons\tests\collections;

use ArrayIterator;
use js\tools\commons\collections\Collection;
use js\tools\commons\collections\Option;
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
	
	public function testClone(): void
	{
		$data = ['foo' => 1];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		$clone = clone $collection;
		
		$this->assertSame($collection->get(), $data);
		$this->assertSame($collection->get(), $clone->get());
		$this->assertNotSame($collection, $clone);
	}
	
	public function testSize(): void
	{
		$data = [1, 2, 3];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$this->assertSame(3, $collection->size());
	}
	
	public function testContainsValue(): void
	{
		$data = [1, 2, 3];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$this->assertTrue($collection->containsValue(3));
		$this->assertFalse($collection->containsValue(4));
		$this->assertFalse($collection->containsValue('1')); // Strict type check.
	}
	
	public function testContainsKey(): void
	{
		$data = [1, 'foo' => 2];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$this->assertTrue($collection->containsKey(0));
		$this->assertTrue($collection->containsKey('foo'));
		// https://www.php.net/manual/en/language.types.array.php
		// Numeric string keys are cast to integers.
		$this->assertTrue($collection->containsKey('0'));
		$this->assertFalse($collection->containsKey(1));
	}
	
	public function testGetValue(): void
	{
		$data = [1, 'foo' => 2];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		$firstValue = $collection->getValue(0);
		$secondValue = $collection->getValue('foo');
		$invalidValue = $collection->getValue('nope');
		
		$this->assertInstanceOf(Option::class, $firstValue);
		$this->assertInstanceOf(Option::class, $secondValue);
		$this->assertInstanceOf(Option::class, $invalidValue);
		
		$this->assertTrue($firstValue->isFound());
		$this->assertTrue($secondValue->isFound());
		$this->assertFalse($invalidValue->isFound());
		
		$this->assertSame($data[0], $firstValue->get());
		$this->assertSame($data['foo'], $secondValue->get());
	}
	
	public function testGetKey(): void
	{
		$data = [1, 'foo' => 2];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$intKey = $collection->getKey(1);
		$stringKey = $collection->getKey(2);
		$invalidKey = $collection->getKey('bar');
		
		$this->assertContainsOnlyInstancesOf(Option::class, [$intKey, $stringKey, $invalidKey]);
		
		$this->assertTrue($intKey->isFound());
		$this->assertTrue($stringKey->isFound());
		$this->assertFalse($invalidKey->isFound());
		
		$this->assertSame(0, $intKey->get());
		$this->assertSame('foo', $stringKey->get());
	}
	
	public function testGetKeys(): void
	{
		$data = [
			1,
			'foo' => 2,
			'bar' => 1,
			'baz' => 3,
			'qux' => 1,
		];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$valueToSearch = 1;
		$allKeys = $collection->getKeys($valueToSearch);
		$firstTwoKeys = $collection->getKeys($valueToSearch, 2);
		$lastTwoKeys = $collection->getKeys($valueToSearch, -2);
		
		$this->assertSame([0, 'bar', 'qux'], $allKeys);
		$this->assertSame([0, 'bar'], $firstTwoKeys);
		$this->assertSame(['bar', 'qux'], $lastTwoKeys);
	}
	
	public function testFindValueInt(): void
	{
		$data = [
			1,
			'foo' => 2,
			'bar' => 3,
			'baz' => 4,
			'qux' => 5,
		];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		$isEven = fn (int $value): bool => ($value % 2 === 0);
	
		$firstValue = $collection->findValue($isEven);
		$lastValue = $collection->findValue($isEven, false);
		
		$this->assertSame(2, $firstValue->get());
		$this->assertSame(4, $lastValue->get());
	}
	
	public function testFindValueNull(): void
	{
		$data = [
			1,
			'foo' => 2,
			'bar' => null,
			'baz' => 3,
			'qux' => 4,
		];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		$nullValue = $collection->findValue(fn ($value) => ($value === null));
		
		$this->assertTrue($nullValue->isFound());
	}
	
	public function testFindKeyInt(): void
	{
		$data = [
			1,
			'foo' => 2,
			'bar' => 3,
			'baz' => 4,
			'qux' => 5,
		];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		$isOdd = fn (int $value): bool => ($value % 2 === 1);
		
		$firstKey = $collection->findKey($isOdd);
		$lastKey = $collection->findKey($isOdd, false);
		
		$this->assertSame(0, $firstKey->get());
		$this->assertSame('qux', $lastKey->get());
	}
	
	public function testFindKeyNull(): void
	{
		$data = [
			1,
			'foo' => 2,
			'bar' => null,
			'baz' => 3,
			'qux' => 4,
		];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		$keyWithNullValue = $collection->findKey(fn ($value) => ($value === null));
		
		$this->assertTrue($keyWithNullValue->isFound());
		$this->assertSame('bar', $keyWithNullValue->get());
	}
}
