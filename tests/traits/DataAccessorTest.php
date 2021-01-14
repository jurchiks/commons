<?php
namespace js\tools\commons\tests\traits;

use Error;
use js\tools\commons\traits\DataAccessor;
use PHPUnit\Framework\TestCase;
use stdClass;

class Accessor
{
	use DataAccessor;
	
	public function __construct(array $data)
	{
		$this->init($data);
	}
}

class DataAccessorTest extends TestCase
{
	public function testInit(): void
	{
		$data = ['foo' => 1];
		$accessor = new Accessor($data);
		
		$this->assertSame($data, $accessor->getAll());
	}
	
	public function testLoadDefault(): void
	{
		$accessor = new class
		{
			use DataAccessor;
		};
		
		$this->assertSame([], $accessor->getAll());
	}
	
	public function testLoadOverloaded(): void
	{
		$accessor = new class
		{
			use DataAccessor;
			
			protected function load(): array
			{
				return ['foo' => 1];
			}
		};
		
		$this->assertSame(['foo' => 1], $accessor->getAll());
	}
	
	public function isEmptyDataset(): iterable
	{
		yield [[], true];
		yield [['foo' => 1], false];
	}
	
	/** @dataProvider isEmptyDataset */
	public function testIsEmpty(array $data, bool $isEmpty): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($isEmpty, $accessor->isEmpty());
	}
	
	public function sizeDataset(): iterable
	{
		yield [[], 0];
		yield [['foo' => 1], 1];
		yield [['foo' => 1, 'bar' => 2, 'baz' => 3], 3];
	}
	
	/** @dataProvider sizeDataset */
	public function testSize(array $data, int $size): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($size, $accessor->size());
	}
	
	public function existsDataset(): iterable
	{
		yield [[], 'foo', false];
		yield [['foo' => 1], 'foo', true];
		yield [['foo' => ['bar' => ['baz' => 1]]], 'foo.bar.baz', true];
	}
	
	/** @dataProvider existsDataset */
	public function testExists(array $data, string $key, bool $exists): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($exists, $accessor->exists($key));
	}
	
	public function getDataset(): iterable
	{
		yield [[], 'foo', null];
		yield [['foo' => 1], 'no such key', null];
		yield [['foo' => 1], 'foo', 1];
		yield [['foo' => ['bar' => ['baz' => 1]]], 'foo.bar.baz', 1];
	}
	
	/** @dataProvider getDataset */
	public function testGet(array $data, string $key, $expectedValue): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($expectedValue, $accessor->get($key));
	}
	
	public function getIntDataset(): iterable
	{
		yield [[], 'no such key', 0];
		
		foreach ([1, 1.25, '1.7', '1e0'] as $numericOne)
		{
			yield [['foo' => $numericOne], 'foo', 1];
		}
		
		foreach ([null, true, '7.62x51', '101 dalmatians', 'not numeric at all', new stdClass()] as $notNumeric)
		{
			yield [['bar' => $notNumeric], 'bar', 0];
		}
	}
	
	/** @dataProvider getIntDataset */
	public function testGetInt(array $data, string $key, $expectedValue): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($expectedValue, $accessor->getInt($key));
	}
	
	public function getFloatDataset(): iterable
	{
		yield [[], 'no such key', 0.0];
		
		yield [['foo' => 1], 'foo', 1.0];
		yield [['foo' => 1.25], 'foo', 1.25];
		yield [['foo' => '13.7'], 'foo', 13.7];
		yield [['foo' => '10e1'], 'foo', 100.0];
		
		foreach ([null, true, '7.62x51', '101 dalmatians', 'not numeric at all', new stdClass()] as $notNumeric)
		{
			yield [['bar' => $notNumeric], 'bar', 0.0];
		}
	}
	
	/** @dataProvider getFloatDataset */
	public function testGetFloat(array $data, string $key, $expectedValue): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($expectedValue, $accessor->getFloat($key));
	}
	
	public function getStringDataset(): iterable
	{
		yield [[], 'no such key', ''];
		
		yield [['foo' => null], 'foo', ''];
		yield [['foo' => 1], 'foo', '1'];
		yield [['foo' => 1.25], 'foo', '1.25'];
		yield [['foo' => true], 'foo', '1'];
		yield [['foo' => false], 'foo', ''];
		yield [['foo' => 'a string'], 'foo', 'a string'];
		yield [
			[
				'foo' => new class
				{
					public function __toString(): string
					{
						return 'a string';
					}
				},
			],
			'foo',
			'a string',
		];
	}
	
	/** @dataProvider getStringDataset */
	public function testGetStringValid(array $data, string $key, $expectedValue): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($expectedValue, $accessor->getString($key));
	}
	
	public function testGetStringArrayConversion(): void
	{
		if (PHP_MAJOR_VERSION === 8)
		{
			$this->expectWarning();
			$this->expectWarningMessage('Array to string conversion');
		}
		else
		{
			$this->expectNotice();
			$this->expectNoticeMessage('Array to string conversion');
		}
		
		$accessor = new Accessor(['foo' => []]);
		$accessor->getString('foo');
	}
	
	public function testGetStringObject(): void
	{
		$this->expectException(Error::class);
		$this->expectExceptionMessage('Object of class stdClass could not be converted to string');
		
		$accessor = new Accessor(['foo' => new stdClass()]);
		$accessor->getString('foo');
	}
	
	public function getBoolDataset(): iterable
	{
		yield [[], 'no such key', false];
		
		foreach ([null, false, '', 0, 0.0, '0', []] as $falsyValues)
		{
			yield [['foo' => $falsyValues], 'foo', false];
		}
		
		foreach (['0.0'/* <-- YUP!!! */, 0.1, 1, true, '7.62x51', ['not empty'], new stdClass()] as $truthyValues)
		{
			yield [['bar' => $truthyValues], 'bar', true];
		}
	}
	
	/** @dataProvider getBoolDataset */
	public function testGetBool(array $data, string $key, $expectedValue): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($expectedValue, $accessor->getBool($key));
	}
	
	public function getArrayDataset(): iterable
	{
		yield [['foo' => 'bar'], 'no such key', []];
		yield [['foo' => 'not an array'], 'foo', []];
		yield [['foo' => []], 'foo', []];
		yield [['foo' => ['bar' => 'baz']], 'foo', ['bar' => 'baz']];
	}
	
	/** @dataProvider getArrayDataset */
	public function testGetArray(array $data, string $key, $expectedValue): void
	{
		$accessor = new Accessor($data);
		
		$this->assertSame($expectedValue, $accessor->getArray($key));
	}
}
