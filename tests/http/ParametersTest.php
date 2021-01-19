<?php
namespace js\tools\commons\tests\http;

use js\tools\commons\http\Parameters;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
	public function testCopy(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);
		$copy = $parameters->copy();
		
		$this->assertInstanceOf(Parameters::class, $copy);
		$this->assertNotSame($copy, $parameters);
	}
	
	public function testJsonSerialize(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);
		
		$this->assertSame('{"foo":"bar"}', json_encode($parameters));
	}
}
