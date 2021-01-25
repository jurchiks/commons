<?php
namespace js\tools\commons\tests\templating;

use js\tools\commons\exceptions\TemplateException;
use js\tools\commons\templating\Engine;
use js\tools\commons\templating\Extension;
use js\tools\commons\templating\Template;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
	public function testGetTemplate(): void
	{
		$engine = new Engine(__DIR__ . '/templates');
		$template = $engine->getTemplate('basic.phtml');
		
		$this->assertInstanceOf(Template::class, $template);
	}
	
	public function testGetTemplateWithoutExtension(): void
	{
		$engine = new Engine(__DIR__ . '/templates');
		$template = $engine->getTemplate('basic');
		
		$this->assertInstanceOf(Template::class, $template);
	}
	
	public function testGetTemplateNotFound(): void
	{
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Invalid template path "not-found.phtml"');
		
		$engine = new Engine(__DIR__ . '/templates');
		$engine->getTemplate('not-found.phtml');
	}
	
	public function testMultipleRoots(): void
	{
		$engine = new Engine(__DIR__ . '/templates/single_parent');
		$engine->addRoot(__DIR__ . '/templates/multi_parent');
		
		$template = $engine->getTemplate('layout');
		
		$this->assertInstanceOf(Template::class, $template);
	}
	
	public function testCustomFunction(): void
	{
		$engine = new Engine(__DIR__ . '/templates');
		$engine->addFunction('uc', fn (string $value) => strtoupper($value));
		
		$template = $engine->getTemplate('custom_function');
		
		$this->assertSame('UPPERCASE', $engine->callFunction('uc', ['Uppercase']));
		$this->assertSame('UPPERCASE', $template->uc('Uppercase'));
		$this->assertSame("<h1>BASIC TEMPLATE</h1>\n", $template->render());
	}
	
	public function testExtension(): void
	{
		$engine = new Engine(__DIR__ . '/templates');
		$engine->addExtension(
			new class implements Extension
			{
				public function uc(string $value): string
				{
					return strtoupper($value);
				}
			}
		);
		
		$template = $engine->getTemplate('custom_function');
		
		$this->assertSame('UPPERCASE', $engine->callFunction('uc', ['Uppercase']));
		$this->assertSame('UPPERCASE', $template->uc('Uppercase'));
		$this->assertSame("<h1>BASIC TEMPLATE</h1>\n", $template->render());
	}
	
	public function testMissingFunction(): void
	{
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Undefined template function "foo"');
		
		$engine = new Engine(__DIR__ . '/templates');
		$engine->callFunction('foo', ['bar']);
	}
	
	public function testRender(): void
	{
		$engine = new Engine(__DIR__ . '/templates');
		
		$this->assertSame("<h1>Basic template</h1>\n", $engine->render('basic'));
	}
	
	public function testRenderRelativePaths()
	{
		$engine = new Engine(__DIR__ . '/templates/engine_root');
		
		$this->assertSame("<p>Child template</p>\n", $engine->render('child'));
	}
}
