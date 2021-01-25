<?php
namespace js\tools\commons\tests\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\http\Request;
use js\tools\commons\http\Url;
use js\tools\commons\upload\UploadedFile;
use PHPUnit\Framework\TestCase;

class RequestWithParamRequestBody extends Request
{
	protected static function getRequestBody(): string
	{
		return 'foo=bar';
	}
}

class RequestWithJsonRequestBody extends Request
{
	protected static function getRequestBody(): string
	{
		return json_encode(['foo' => 'bar']);
	}
}

class RequestTest extends TestCase
{
	public function tearDown(): void
	{
		unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTPS'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $_SERVER['CONTENT_TYPE']);
		$_GET = [];
		$_POST = [];
	}
	
	public function testValidRequest(): void
	{
		$files = [
			'foo' => [
				'name'     => 'example.txt',
				'type'     => 'text/plain',
				'tmp_name' => '/path/to/tmp/directory/phpC887.tmp',
				'error'    => UPLOAD_ERR_OK,
				'size'     => 666,
			],
		];
		$request = new Request('post', new Url('https://host.name/upload'), [], $files, 'https://host.name/');
		
		$this->assertSame('post', $request->getMethod());
		$this->assertTrue($request->isMethod('POST'));
		$this->assertSame('https://host.name/upload', $request->getUrl()->getAbsolute());
		$this->assertTrue($request->isSecure());
		$this->assertSame([], $request->getData()->getAll());
		$this->assertSame('https://host.name/', $request->getReferer());
		
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $request->getFiles()->get('foo');
		$this->assertInstanceOf(UploadedFile::class, $uploadedFile);
		$this->assertSame('example.txt', $uploadedFile->getName());
	}
	
	public function testInvalidRequestMethod(): void
	{
		$this->expectException(HttpException::class);
		$this->expectExceptionMessage('Unsupported request method "foo"');
		
		new Request('foo', new Url('https://host.name/upload'), []);
	}
	
	public function testCreateFromGlobalsGet(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		$_GET = ['bar' => 'baz'];
		
		$request = Request::createFromGlobals();
		
		$this->assertSame('get', $request->getMethod());
		$this->assertTrue($request->isMethod('GET'));
		$this->assertSame('http://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertFalse($request->isSecure());
		$this->assertSame(['bar' => 'baz'], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	public function testCreateFromGlobalsPost(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		$_POST = ['bar' => 'baz'];
		
		$request = Request::createFromGlobals();
		
		$this->assertSame('post', $request->getMethod());
		$this->assertTrue($request->isMethod('POST'));
		$this->assertSame('https://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertTrue($request->isSecure());
		$this->assertSame(['bar' => 'baz'], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	public function testCreateFromGlobalsPut(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		
		$request = RequestWithParamRequestBody::createFromGlobals();
		
		$this->assertSame('put', $request->getMethod());
		$this->assertTrue($request->isMethod('PUT'));
		$this->assertSame('http://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertFalse($request->isSecure());
		$this->assertSame(['foo' => 'bar'], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	public function testCreateFromGlobalsPatchJson(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'PATCH';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		$_SERVER['CONTENT_TYPE'] = 'application/json';
		
		$request = RequestWithJsonRequestBody::createFromGlobals();
		
		$this->assertSame('patch', $request->getMethod());
		$this->assertTrue($request->isMethod('PATCH'));
		$this->assertSame('http://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertFalse($request->isSecure());
		$this->assertSame(['foo' => 'bar'], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	public function testCreateFromGlobalsDelete(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		
		$request = Request::createFromGlobals();
		
		$this->assertSame('delete', $request->getMethod());
		$this->assertTrue($request->isMethod('DELETE'));
		$this->assertSame('http://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertFalse($request->isSecure());
		$this->assertSame([], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	public function testCreateFromGlobalsMissingData(): void
	{
		$this->expectException(HttpException::class);
		$this->expectExceptionMessage(
			'Missing required fields in global $_SERVER - [REQUEST_METHOD, HTTP_HOST, REQUEST_URI]'
		);
		
		Request::createFromGlobals();
	}
	
	public function testCreateFromGlobalsInvalidRequestMethod(): void
	{
		$this->expectException(HttpException::class);
		$this->expectExceptionMessage('Unsupported request method "foo"');
		
		$_SERVER['REQUEST_METHOD'] = 'foo';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		
		Request::createFromGlobals();
	}
}
