<?php
namespace js\tools\commons\tests\templating;

use js\tools\commons\exceptions\TemplateException;
use js\tools\commons\templating\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
	public function testInvalidPath(): void
	{
		$path = __DIR__ . '/templates/no_such_file.phtml';
		
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Invalid template path ' . $path);
		
		new Template($path);
	}
	
	public function testRenderBasic(): void
	{
		$template = new Template(__DIR__ . '/templates/basic.phtml');
		
		$this->assertSame("<h1>Basic template</h1>\n", $template->render());
	}
	
	public function testRenderToString(): void
	{
		$template = new Template(__DIR__ . '/templates/basic.phtml');
		
		$this->assertSame("<h1>Basic template</h1>\n", strval($template));
	}
	
	public function testTemplateNameWithoutExtension(): void
	{
		$template = new Template(__DIR__ . '/templates/basic');
		
		$this->assertSame("<h1>Basic template</h1>\n", $template->render());
	}
	
	public function testInclude(): void
	{
		$template = new Template(__DIR__ . '/templates/includer.phtml');
		
		$this->assertSame("<h1>Basic template</h1>\n", $template->render());
	}
	
	public function testParent(): void
	{
		$template = new Template(__DIR__ . '/templates/single_parent/child.phtml');
		
		$this->assertSame("<p>Child template</p>\n", $template->render());
	}
	
	public function testParentWithoutChild(): void
	{
		$template = new Template(__DIR__ . '/templates/single_parent/parent.phtml');
		
		$this->assertSame('', $template->render());
	}
	
	public function testMultipleParents(): void
	{
		$template = new Template(__DIR__ . '/templates/multi_parent/child.phtml');
		$expectedOutput = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Multi parent template test</title>
</head>
<body>
<header>
	<div class="logo"></div>
	<div class="page-title"></div>
</header>
<main>
<h4>Child template heading</h4>
<p>Child template content</p>
</main>
</body>
</html>

HTML;
		
		$this->assertSame($expectedOutput, $template->render());
	}
	
	public function testBlocks(): void
	{
		$template = new Template(__DIR__ . '/templates/blocks/child.phtml');
		$expectedOutput = <<<HTML
<header>
	<div class="logo"></div>
	<div class="page-title"></div>
</header>

HTML;
		
		$this->assertSame($expectedOutput, $template->render());
	}
	
	public function testNestedBlocks(): void
	{
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Nested blocks are not allowed');
		
		$template = new Template(__DIR__ . '/templates/blocks/nested.phtml');
		$template->render();
	}
	
	public function testUnclosedBlock(): void
	{
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Unclosed block "not-closed"');
		
		$template = new Template(__DIR__ . '/templates/blocks/unclosed.phtml');
		$template->render();
	}
	
	public function testData(): void
	{
		$template = new Template(__DIR__ . '/templates/with_data.phtml', ['foo' => 'bar']);
		
		$this->assertSame('{"foo":"bar"}', $template->render());
		$this->assertTrue(isset($template->foo));
		$this->assertSame('bar', $template->foo);
	}
	
	public function testInvalidDataAccess(): void
	{
		$template = new Template(__DIR__ . '/templates/basic.phtml');
		
		$this->assertNull($template->foo);
	}
	
	public function testDataWrite(): void
	{
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Template values are read-only');
		
		$template = new Template(__DIR__ . '/templates/basic.phtml');
		$template->foo = 'bar';
	}
	
	public function testCustomMethodWithoutEngine(): void
	{
		$this->expectException(TemplateException::class);
		$this->expectExceptionMessage('Callbacks are only available with Engine');
		
		$template = new Template(__DIR__ . '/templates/basic.phtml');
		$template->foo();
	}
}
