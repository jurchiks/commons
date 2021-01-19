<?php
namespace js\tools\commons\tests\traits;

use js\tools\commons\traits\DataWriter;
use PHPUnit\Framework\TestCase;

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
		
		$this->assertSame(
			[
				'foo' => 'bar',
				'baz' => true,
				'qux' => ['quux' => ['quuux' => 'magic!']],
			],
			$writer->getAll()
		);
		$this->assertSame('bar', $writer->get('foo'));
		$this->assertSame(true, $writer->get('baz'));
		$this->assertSame(['quux' => ['quuux' => 'magic!']], $writer->get('qux'));
		$this->assertSame(['quuux' => 'magic!'], $writer->get('qux.quux'));
		$this->assertSame('magic!', $writer->get('qux.quux.quuux'));
	}
}
