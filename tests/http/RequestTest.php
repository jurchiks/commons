<?php
namespace js\tools\commons\tests\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\http\Request;
use js\tools\commons\http\Url;
use js\tools\commons\upload\UploadedFile;
use JsonException;
use PHPUnit\Framework\TestCase;

class RequestWithUrlEncodedBody extends Request
{
	protected static function getRequestBody(): string
	{
		return 'foo=bar';
	}
}

class RequestWithJsonBody extends Request
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
		$request = new Request('POST', new Url('https://host.name/upload'), [], $files, 'https://host.name/');
		
		$this->assertSame('POST', $request->getMethod());
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
		
		$request = Request::createFromGlobals();
		
		$this->assertSame('GET', $request->getMethod());
		$this->assertTrue($request->isMethod('GET'));
		$this->assertSame('http://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertFalse($request->isSecure());
		$this->assertSame([], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	public function methodsWithRequestBodyDataset(): iterable
	{
		yield ['POST'];
		yield ['PUT'];
		yield ['PATCH'];
		yield ['DELETE'];
	}
	
	/** @dataProvider methodsWithRequestBodyDataset() */
	public function testCreateFromGlobalsFormSubmit(string $method): void
	{
		$_SERVER['REQUEST_METHOD'] = $method;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		
		$request = RequestWithUrlEncodedBody::createFromGlobals();
		
		$this->assertSame($method, $request->getMethod());
		$this->assertTrue($request->isMethod($method));
		$this->assertSame('https://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertTrue($request->isSecure());
		$this->assertSame(['foo' => 'bar'], $request->getData()->getAll());
		$this->assertSame('', $request->getReferer());
	}
	
	/** @dataProvider methodsWithRequestBodyDataset() */
	public function testCreateFromGlobalsJsonBody(string $method): void
	{
		$_SERVER['REQUEST_METHOD'] = $method;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		$_SERVER['CONTENT_TYPE'] = 'application/json';
		
		$request = RequestWithJsonBody::createFromGlobals();
		
		$this->assertSame($method, $request->getMethod());
		$this->assertTrue($request->isMethod($method));
		$this->assertSame('https://host.name/foo', $request->getUrl()->getAbsolute());
		$this->assertTrue($request->isSecure());
		$this->assertSame(['foo' => 'bar'], $request->getData()->getAll());
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
	
	public function testCreateFromGlobalsInvalidJsonBody(): void
	{
		$this->expectException(JsonException::class);
		
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['HTTP_HOST'] = 'host.name';
		$_SERVER['REQUEST_URI'] = '/foo';
		$_SERVER['CONTENT_TYPE'] = 'application/json';
		
		Request::createFromGlobals();
	}
}
