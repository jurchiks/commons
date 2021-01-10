<?php
namespace js\tools\commons\tests\collections;

use js\tools\commons\collections\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
	public function testConstructorWithArray(): void
	{
		$data = ['foo' => 1];
		$collection = $this->getMockForAbstractClass(Collection::class, [$data]);
		
		$this->assertSame($collection->get(), $data);
	}
}
