<?php
namespace js\tools\commons\tests\datetime;

use InvalidArgumentException;
use js\tools\commons\datetime\ShortInterval;
use PHPUnit\Framework\TestCase;

class ShortIntervalTest extends TestCase
{
	public function validIntervalsDataset(): iterable
	{
		yield ['1y', ['y' => 1], null];
		yield ['1m', ['m' => 1], null];
		yield ['1d', ['d' => 1], null];
		yield ['1h', ['h' => 1], null];
		yield ['1i', ['i' => 1], null];
		yield ['1s', ['s' => 1], null];
		yield ['1y1m1d', ['y' => 1, 'm' => 1, 'd' => 1], null];
		yield ['1h1i1s', ['h' => 1, 'i' => 1, 's' => 1], null];
		yield ['1y1m1d1h1i1s', ['y' => 1, 'm' => 1, 'd' => 1, 'h' => 1, 'i' => 1, 's' => 1], null];
		
		// Special cases:
		yield ['1d1m1s', ['d' => 1, 'i' => 1, 's' => 1], '1d1i1s']; // If m is after d, it is resolved as minutes.
		yield ['1h1m1s', ['h' => 1, 'i' => 1, 's' => 1], '1h1i1s']; // If m is after h, it is resolved as minutes.
		yield ['1y1m1d1h1m1s', ['y' => 1, 'm' => 1, 'd' => 1, 'h' => 1, 'i' => 1, 's' => 1], '1y1m1d1h1i1s'];
		yield ['69h69i69s', ['h' => 69, 'i' => 69, 's' => 69], null]; // Overflows not resolved.
		yield ['69m69s', ['m' => 69, 's' => 69], null]; // First matching segment = months, not minutes.
	}
	
	/** @dataProvider validIntervalsDataset */
	public function testParse(string $shortInterval, array $expectedProperties): void
	{
		static $defaultProperties = [
			'y' => 0,
			'm' => 0,
			'd' => 0,
			'h' => 0,
			'i' => 0,
			's' => 0,
		];
		$expectedProperties = array_merge($defaultProperties, $expectedProperties);
		
		$interval = ShortInterval::parse($shortInterval);
		$actualProperties = array_filter(
			get_object_vars($interval),
			fn ($property) => array_key_exists($property, $defaultProperties),
			ARRAY_FILTER_USE_KEY
		);
		
		$this->assertSame($expectedProperties, $actualProperties);
	}
	
	/** @dataProvider validIntervalsDataset */
	public function testReverseParse(string $shortInterval, $_, ?string $reversedInterval): void
	{
		$interval = ShortInterval::parse($shortInterval);
		$newShortInterval = ShortInterval::build($interval);
		
		$this->assertSame($reversedInterval ?? $shortInterval, $newShortInterval);
	}
	
	public function invalidIntervalsDataset(): iterable
	{
		yield [''];
		yield ['12'];
		yield ['12d34'];
	}
	
	/** @dataProvider invalidIntervalsDataset */
	public function testInvalidIntervals(string $invalidInterval): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid interval: "' . $invalidInterval . '"');
		
		ShortInterval::parse($invalidInterval);
	}
}
