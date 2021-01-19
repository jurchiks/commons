<?php
namespace js\tools\commons\tests\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\UriException;
use js\tools\commons\http\Uri;
use PHPUnit\Framework\TestCase;
use stdClass;

class UriTest extends TestCase
{
	public function invalidUrlDataset(): iterable
	{
		yield ['http:///example.com'];
		yield ['http://:80'];
		yield ['http://user@:80'];
		yield ['foo:bar@domain.tld'];
	}
	
	/** @dataProvider invalidUrlDataset */
	public function testInvalidUrls(string $invalidUrl): void
	{
		$this->expectException(UriException::class);
		
		new Uri($invalidUrl);
	}
	
	public function testUnsupportedScheme(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Unsupported URI scheme "foo"');
		
		new Uri('foo://bar');
	}
	
	public function testAbsoluteUrl(): void
	{
		$resource = '/path?arg=value#fragment';
		$urlString = 'http://username:password@hostname:9090' . $resource;
		$uri = new Uri($urlString);
		$this->assertSame('http', $uri->getScheme());
		$this->assertSame('username', $uri->getUsername());
		$this->assertSame('password', $uri->getPassword());
		$this->assertSame('hostname', $uri->getHost());
		$this->assertSame(9090, $uri->getPort());
		$this->assertSame('/path', $uri->getPath());
		$this->assertSame('?arg=value', $uri->getQuery());
		$this->assertSame(['arg' => 'value'], $uri->getQueryParameters()->getAll());
		$this->assertSame('fragment', $uri->getFragment());
		$this->assertSame($urlString, $uri->getAbsolute());
		$this->assertSame($resource, $uri->getRelative());
		$this->assertSame($resource, $uri->get());
		$this->assertSame($resource, strval($uri));
		$this->assertTrue($uri->isAbsolute());
	}
	
	public function testRelativeUrl(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Uri($resource);
		$this->assertSame('', $uri->getScheme());
		$this->assertSame('', $uri->getUsername());
		$this->assertSame('', $uri->getPassword());
		$this->assertSame('', $uri->getHost());
		$this->assertSame(0, $uri->getPort());
		$this->assertSame('/path', $uri->getPath());
		$this->assertSame('?arg=value', $uri->getQuery());
		$this->assertSame(['arg' => 'value'], $uri->getQueryParameters()->getAll());
		$this->assertSame('fragment', $uri->getFragment());
		$this->assertSame($resource, $uri->getRelative());
		$this->assertSame($resource, $uri->get());
		$this->assertSame($resource, strval($uri));
		$this->assertFalse($uri->isAbsolute());
	}
	
	public function testSourceOnly(): void
	{
		$urlString = 'http://username:password@hostname:9090';
		$uri = new Uri($urlString);
		$this->assertSame('http', $uri->getScheme());
		$this->assertSame('username', $uri->getUsername());
		$this->assertSame('password', $uri->getPassword());
		$this->assertSame('hostname', $uri->getHost());
		$this->assertSame(9090, $uri->getPort());
		$this->assertSame('/', $uri->getPath());
		$this->assertSame('', $uri->getQuery());
		$this->assertSame([], $uri->getQueryParameters()->getAll());
		$this->assertSame('', $uri->getFragment());
		$this->assertSame($urlString . '/', $uri->getAbsolute());
		$this->assertSame('/', $uri->getRelative());
		$this->assertSame('/', $uri->get());
		$this->assertSame('/', strval($uri));
		$this->assertTrue($uri->isAbsolute());
	}
	
	public function createFromGlobalsDataset(): iterable
	{
		yield ['domain.tld', '/foo?bar=baz', 'on', 'https://domain.tld/foo?bar=baz'];
		yield ['domain.tld', '/', null, 'http://domain.tld/'];
	}
	
	/** @dataProvider createFromGlobalsDataset */
	public function testCreateFromGlobalsValid(string $host, string $requestUri, $https, $expectedUrl): void
	{
		$_SERVER['HTTP_HOST'] = $host;
		$_SERVER['REQUEST_URI'] = $requestUri;
		$_SERVER['HTTPS'] = $https;
		
		$uri = Uri::createFromGlobals();
		
		$this->assertSame($expectedUrl, $uri->getAbsolute());
		
		unset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $_SERVER['HTTPS']);
	}
	
	public function testCreateFromGlobalsInvalid(): void
	{
		$this->expectException(HttpException::class);
		$this->expectExceptionMessage('Missing required fields in global $_SERVER - [HTTP_HOST, REQUEST_URI]');
		
		Uri::createFromGlobals();
	}
	
	public function testCopy(): void
	{
		$uri = new Uri('http://username:password@hostname:9090/path?arg=value#fragment');
		$copy = $uri->copy();
		
		$this->assertInstanceOf(Uri::class, $copy);
		$this->assertNotSame($uri, $copy);
		$this->assertSame($uri->getAbsolute(), $copy->getAbsolute());
	}
	
	public function testQueryEncoding(): void
	{
		$uri = new Uri('?arg=value with spaces');
		
		$this->assertSame('?arg=value+with+spaces', $uri->getQuery());
		$this->assertSame('?arg=value%20with%20spaces', $uri->getQuery(true));
		$this->assertSame(['arg' => 'value with spaces'], $uri->getQueryParameters()->getAll());
	}
	
	public function testInvalidAbsoluteUrl(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Cannot make an absolute URL without host');
		
		(new Uri('/path?arg=value#fragment'))->getAbsolute();
	}
	
	public function testWithoutScheme(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$uri = new Uri($urlString);
		
		$this->assertSame('//' . $urlString, $uri->getAbsolute());
	}
	
	public function testSetSchemeValidChanged(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$uri = new Uri('http://' . $urlString);
		$uri->setScheme('    HTTPS    ');
		
		$this->assertSame('https://' . $urlString, strval($uri));
	}
	
	public function testSetSchemeValidSame(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Uri('http://hostname:9090' . $resource);
		$uri->setScheme('http');
		
		$this->assertSame($resource, strval($uri));
	}
	
	public function testSetSchemeInvalid(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Unsupported URI scheme "foo"');
		
		$uri = new Uri('http://hostname:9090/path?arg=value#fragment');
		$uri->setScheme('foo');
	}
	
	public function testSetAuthValidSame(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Uri('http://foo:bar@hostname:9090' . $resource);
		$uri->setAuth('foo', 'bar');
		
		$this->assertSame($resource, strval($uri));
	}
	
	public function testSetAuthValidChanged(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$uri = new Uri('http://foo:bar@' . $urlString);
		$uri->setAuth('baz', 'qux');
		
		$this->assertSame('http://baz:qux@' . $urlString, strval($uri));
	}
	
	public function testSetAuthInvalidUsername(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid auth credentials');
		
		$uri = new Uri('http://hostname:9090/path?arg=value#fragment');
		$uri->setAuth('foo:bar');
	}
	
	public function testSetAuthMissingUsername(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Cannot have a password without a username');
		
		$uri = new Uri('http://hostname:9090/path?arg=value#fragment');
		$uri->setAuth('', 'foo');
	}
	
	public function testSetHostValidSame(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Uri('http://hostname:9090' . $resource);
		$uri->setHost('//HOSTNAME/');
		
		$this->assertSame($resource, strval($uri));
	}
	
	public function testSetHostValidChanged(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Uri('http://hostname:9090' . $resource);
		$uri->setHost('host.name');
		
		$this->assertSame('http://host.name:9090' . $resource, strval($uri));
	}
	
	public function invalidHostDataset(): iterable
	{
		yield [''];
		yield ['host/name'];
		yield ['127.0.0.-1'];
		yield ['это-не-ascii'];
	}
	
	/** @dataProvider invalidHostDataset */
	public function testSetHostInvalid(string $invalidHost): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid host "' . $invalidHost . '"');
		
		$uri = new Uri('http://hostname:9090/path?arg=value#fragment');
		$uri->setHost($invalidHost);
	}
	
	public function testSetPortValidSame(): void
	{
		$uri = new Uri('http://hostname:9090/');
		$uri->setPort(9090);
		
		$this->assertSame('/', strval($uri));
	}
	
	public function testSetPortValidChanged(): void
	{
		$uri = new Uri('http://hostname:9090/');
		$uri->setPort(90);
		
		$this->assertSame('http://hostname:90/', strval($uri));
	}
	
	public function testClearPort(): void
	{
		$uri = new Uri('http://hostname:9090/');
		$uri->setPort(0);
		
		$this->assertSame('http://hostname/', strval($uri));
	}
	
	public function testSetPortInvalid(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid port number "-1"');
		
		$uri = new Uri('http://hostname:9090/');
		$uri->setPort(-1);
	}
	
	public function testSetPathValid(): void
	{
		$uri = new Uri('http://hostname:9090/');
		$uri->setPath('///foo///');
		
		$this->assertSame('/foo', strval($uri));
	}
	
	public function testSetPathInvalid(): void
	{
		$path = '/path?contains=other_bits';
		
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid path "' . $path . '"');
		
		(new Uri('http://hostname:9090/'))->setPath($path);
	}
	
	public function testSetQueryValid(): void
	{
		$uri = new Uri('http://hostname:9090/');
		
		$uri->setQuery('');
		$this->assertSame('/', strval($uri));
		
		$uri->setQuery('foo=bar');
		$this->assertSame('/?foo=bar', strval($uri));
		
		$uri->setQuery('?foo=bar');
		$this->assertSame('/?foo=bar', strval($uri));
	}
	
	public function testSetQueryInvalid(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid query "foo=bar#baz"');
		
		$uri = new Uri('hostname:9090');
		$uri->setQuery('foo=bar#baz');
	}
	
	public function testSetQueryParametersValid(): void
	{
		$queryParameters = ['foo' => ['bar' => 'baz']];
		
		$uri = new Uri('http://hostname:9090/');
		$uri->setQueryParameters($queryParameters);
		
		$this->assertSame($queryParameters, $uri->getQueryParameters()->getAll());
		$this->assertSame('/?foo%5Bbar%5D=baz', strval($uri));
	}
	
	public function invalidQueryParameterDataset(): iterable
	{
		yield [null];
		yield [new stdClass()];
	}
	
	/** @dataProvider invalidQueryParameterDataset */
	public function testSetQueryParametersInvalid($invalidQueryParameter): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage(
			'Invalid query parameter "' . gettype($invalidQueryParameter) . '" for key "foo"'
		);
		
		$uri = new Uri('http://hostname:9090/');
		$uri->setQueryParameters(['foo' => $invalidQueryParameter]);
	}
	
	public function testSetQueryParameterValid(): void
	{
		$uri = new Uri('http://hostname:9090/?foo=bar');
		$uri->setQueryParameter('foo', ['bar' => 'baz']);
		
		$this->assertSame(['foo' => ['bar' => 'baz']], $uri->getQueryParameters()->getAll());
		$this->assertSame('/?foo%5Bbar%5D=baz', strval($uri));
	}
	
	/** @dataProvider invalidQueryParameterDataset */
	public function testSetQueryParameterInvalid($invalidQueryParameter): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage(
			'Invalid query parameter "' . gettype($invalidQueryParameter) . '" for key "foo"'
		);
		
		$uri = new Uri('http://hostname:9090/?foo=bar');
		$uri->setQueryParameter('foo', $invalidQueryParameter);
	}
	
	public function testSetFragment(): void
	{
		$uri = new Uri('http://hostname:9090/?foo=bar');
		$uri->setFragment('baz');
		
		$this->assertSame('/?foo=bar#baz', strval($uri));
	}
}