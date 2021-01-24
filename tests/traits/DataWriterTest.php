<?php
namespace js\tools\commons\tests\traits;

use InvalidArgumentException;
use js\tools\commons\traits\DataWriter;
use PHPUnit\Framework\TestCase;
use stdClass;

class DataWriterTest extends TestCase
{
	public function testSet(): void
	{
		$writer = new class
		{
			use DataWriter;
		};
		$writer->set('foo', 'bar');
		$writer->set('baz', true);
		$writer->set('qux.quux.quuux', 'magic!');
		$writer->set(0, 'int');
		$writer->set([5, 'foo', 1], 'nested int keys');
		
		$this->assertSame(
			[
				'foo' => 'bar',
				'baz' => true,
				'qux' => ['quux' => ['quuux' => 'magic!']],
				0     => 'int',
				5     => ['foo' => [1 => 'nested int keys']],
			],
			$writer->getAll()
		);
	}
	
	public function testSetAppend(): void
	{
		$writer = new class
		{
			use DataWriter;
		};
		$writer->set('foo.bar', 1);
		$writer->set('foo.baz', 2);
		
		$this->assertSame(['foo' => ['bar' => 1, 'baz' => 2]], $writer->getAll());
	}
	
	public function testSetRewrite(): void
	{
		$writer = new class
		{
			use DataWriter;
		};
		$writer->set('foo.bar', ['baz' => 1]);
		$writer->set('foo.bar', 2);
		
		$this->assertSame(['foo' => ['bar' => 2]], $writer->getAll());
	}
	
	public function testSetNonArrayConversion(): void
	{
		$writer = new class
		{
			use DataWriter;
		};
		$writer->set('foo.bar', 1);
		$writer->set('foo.bar.baz', 2);
		
		$this->assertSame(['foo' => ['bar' => [1, 'baz' => 2]]], $writer->getAll());
	}
	
	public function testEmptyArrayKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Key must not be empty');
		
		$writer = new class
		{
			use DataWriter;
		};
		$writer->set([], 'foo');
	}
	
	public function getInvalidKeyDataset(): iterable
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
	
	/** @dataProvider getInvalidKeyDataset */
	public function testInvalidKey($key): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Key must be int or string, got '/* type here */);
		
		$writer = new class
		{
			use DataWriter;
		};
		$writer->set($key, 'foo');
	}
}
