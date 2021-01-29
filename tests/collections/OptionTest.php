<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\None;
use js\tools\commons\collections\Option;
use js\tools\commons\collections\Some;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
	public function testEmpty(): void
	{
		$empty = Option::empty();
		
		$this->assertInstanceOf(None::class, $empty);
	}
	
	public function testOf(): void
	{
		$value = 'some value';
		$notEmpty = Option::of($value);
		
		$this->assertInstanceOf(Some::class, $notEmpty);
		$this->assertSame($value, $notEmpty->get());
	}
	
	public function testOfNullableEmpty(): void
	{
		$empty = Option::ofNullable(null);
		
		$this->assertInstanceOf(None::class, $empty);
	}
	
	public function testOfNullableNotEmpty(): void
	{
		$value = 'some value';
		$notEmpty = Option::ofNullable($value);
		
		$this->assertInstanceOf(Some::class, $notEmpty);
		$this->assertSame($value, $notEmpty->get());
	}
}
