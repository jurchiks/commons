<?php
namespace js\tools\commons\tests\collections;

use InvalidArgumentException;
use js\tools\commons\collections\ArrayHelper;
use js\tools\commons\collections\None;
use js\tools\commons\collections\Some;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArrayHelperTest extends TestCase
{
	public function getFoundDataset(): iterable
	{
		yield [['foo' => 1], 'foo', 1];
		yield [['foo' => null], 'foo', null];
		yield [[1 => 'foo'], 1, 'foo'];
		yield [['foo' => ['bar' => ['baz' => 1]]], 'foo.bar.baz', 1];
		yield [['foo' => [0 => ['baz' => 1]]], ['foo', 0, 'baz'], 1];
	}
	
	/** @dataProvider getFoundDataset */
	public function testGetFound(array $data, $key, $expectedValue): void
	{
		$result = ArrayHelper::get($data, $key);
		
		$this->assertInstanceOf(Some::class, $result);
		$this->assertSame($expectedValue, $result->get());
	}
	
	public function getNotFoundDataset(): iterable
	{
		yield [[], 'foo'];
		yield [['foo' => 1], 'no such key'];
		yield [['foo' => 1], []];
		yield [['foo' => 'bar'], 'foo.bar.baz'];
	}
	
	/** @dataProvider getNotFoundDataset */
	public function testGetNotFound(array $data, $key): void
	{
		$result = ArrayHelper::get($data, $key);
		
		$this->assertInstanceOf(None::class, $result);
	}
	
	public function invalidKeyDataset(): iterable
	{
		yield [null];
		yield [true];
		yield [0.5];
		yield [new stdClass()];
		yield [['foo', null]];
		yield [['foo', true]];
		yield [['foo', 0.5]];
		yield [['foo', new stdClass()]];
	}
	
	/** @dataProvider invalidKeyDataset */
	public function testGetInvalidKey($key): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Key must be int or string, got '/* type here */);
		
		$data = ['foo' => [0 => ['baz' => 1]]];
		
		ArrayHelper::get($data, $key);
	}
	
	/** @dataProvider invalidKeyDataset */
	public function testSetInvalidKey($key): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Key must be int or string, got '/* type here */);
		
		$array = [];
		
		ArrayHelper::set($array, $key, 'foo');
	}
	
	public function testSet(): void
	{
		$array = [];
		
		ArrayHelper::set($array, 'foo', 'bar');
		ArrayHelper::set($array, 'baz', true);
		ArrayHelper::set($array, 'qux.quux.quuux', 'magic!');
		ArrayHelper::set($array, 0, 'int');
		ArrayHelper::set($array, [5, 'foo', 1], 'nested int keys');
		
		$this->assertSame(
			[
				'foo' => 'bar',
				'baz' => true,
				'qux' => ['quux' => ['quuux' => 'magic!']],
				0     => 'int',
				5     => ['foo' => [1 => 'nested int keys']],
			],
			$array
		);
	}
	
	public function testSetAppend(): void
	{
		$array = [];
		
		ArrayHelper::set($array, 'foo.bar', 1);
		ArrayHelper::set($array, 'foo.baz', 2);
		
		$this->assertSame(['foo' => ['bar' => 1, 'baz' => 2]], $array);
	}
	
	public function testSetRewrite(): void
	{
		$array = [];
		
		ArrayHelper::set($array, 'foo.bar', ['baz' => 1]);
		ArrayHelper::set($array, 'foo.bar', 2);
		
		$this->assertSame(['foo' => ['bar' => 2]], $array);
	}
	
	public function testSetNonArrayConversion(): void
	{
		$array = [];
		
		ArrayHelper::set($array, 'foo.bar', 1);
		ArrayHelper::set($array, 'foo.bar.baz', 2);
		
		$this->assertSame(['foo' => ['bar' => [1, 'baz' => 2]]], $array);
	}
	
	public function testEmptyArrayKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Key must not be empty');
		
		$array = [];
		
		ArrayHelper::set($array, [], 'foo');
	}
}
