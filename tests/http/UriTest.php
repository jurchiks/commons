<?php
namespace js\tools\commons\tests\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\UriException;
use js\tools\commons\http\Url;
use PHPUnit\Framework\TestCase;
use stdClass;

class UriTest extends TestCase
{
	public function invalidUrlDataset(): iterable
	{
		yield ['///foo'];
		yield ['http:///domain.tld'];
		yield ['http://:80'];
		yield ['http://user@:80'];
		yield ['foo:bar@domain.tld'];
		yield ['http://domain.tld:90000'];
	}
	
	/** @dataProvider invalidUrlDataset */
	public function testInvalidUrls(string $invalidUrl): void
	{
		$this->expectException(UriException::class);
		
		new Url($invalidUrl);
	}
	
	public function testUnsupportedScheme(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Unsupported URI scheme "foo"');
		
		new Url('foo://bar');
	}
	
	public function testAbsoluteUrl(): void
	{
		$resource = '/path?arg=value#fragment';
		$urlString = 'http://username:password@hostname:9090' . $resource;
		$uri = new Url($urlString);
		$this->assertSame('http', $uri->getScheme());
		$this->assertSame('username', $uri->getUsername());
		$this->assertSame('password', $uri->getPassword());
		$this->assertSame('hostname', $uri->getHost());
		$this->assertSame(9090, $uri->getPort());
		$this->assertSame('/path', $uri->getPath());
		$this->assertSame('?arg=value', $uri->getQuery());
		$this->assertSame(['arg' => 'value'], $uri->getQueryParameters()->getAll());
		$this->assertSame('#fragment', $uri->getFragment());
		$this->assertSame($urlString, $uri->getAbsolute());
		$this->assertSame($resource, $uri->getRelative());
		$this->assertSame($urlString, $uri->get());
		$this->assertSame($urlString, strval($uri));
		$this->assertTrue($uri->isAbsolute());
	}
	
	public function testRelativeUrl(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Url($resource);
		$this->assertSame('', $uri->getScheme());
		$this->assertSame('', $uri->getUsername());
		$this->assertSame('', $uri->getPassword());
		$this->assertSame('', $uri->getHost());
		$this->assertNull($uri->getPort());
		$this->assertSame('/path', $uri->getPath());
		$this->assertSame('?arg=value', $uri->getQuery());
		$this->assertSame(['arg' => 'value'], $uri->getQueryParameters()->getAll());
		$this->assertSame('#fragment', $uri->getFragment());
		$this->assertSame($resource, $uri->getRelative());
		$this->assertSame($resource, $uri->get());
		$this->assertSame($resource, strval($uri));
		$this->assertFalse($uri->isAbsolute());
	}
	
	public function testSourceOnly(): void
	{
		$urlString = 'http://username:password@hostname:9090';
		$uri = new Url($urlString);
		$this->assertSame('http', $uri->getScheme());
		$this->assertSame('username', $uri->getUsername());
		$this->assertSame('password', $uri->getPassword());
		$this->assertSame('hostname', $uri->getHost());
		$this->assertSame(9090, $uri->getPort());
		$this->assertSame('', $uri->getPath());
		$this->assertSame('', $uri->getQuery());
		$this->assertSame([], $uri->getQueryParameters()->getAll());
		$this->assertSame('', $uri->getFragment());
		$this->assertSame($urlString, $uri->getAbsolute());
		$this->assertSame('', $uri->getRelative());
		$this->assertSame($urlString, $uri->get());
		$this->assertSame($urlString, strval($uri));
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
		
		$uri = Url::createFromGlobals();
		
		$this->assertSame($expectedUrl, $uri->getAbsolute());
		
		unset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $_SERVER['HTTPS']);
	}
	
	public function testCreateFromGlobalsInvalid(): void
	{
		$this->expectException(HttpException::class);
		$this->expectExceptionMessage('Missing required fields in global $_SERVER - [HTTP_HOST, REQUEST_URI]');
		
		Url::createFromGlobals();
	}
	
	public function testCopy(): void
	{
		$uri = new Url('http://username:password@hostname:9090/path?arg=value#fragment');
		$copy = $uri->copy();
		
		$this->assertInstanceOf(Url::class, $copy);
		$this->assertNotSame($uri, $copy);
		$this->assertSame($uri->getAbsolute(), $copy->getAbsolute());
	}
	
	public function testQueryEncoding(): void
	{
		$uri = new Url('?arg=value with spaces');
		
		$this->assertSame('?arg=value+with+spaces', $uri->getQuery());
		$this->assertSame('?arg=value%20with%20spaces', $uri->getQuery(true));
		$this->assertSame(['arg' => 'value with spaces'], $uri->getQueryParameters()->getAll());
	}
	
	public function testInvalidAbsoluteUrl(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Cannot make an absolute URL without host');
		
		(new Url('/path?arg=value#fragment'))->getAbsolute();
	}
	
	public function testWithoutScheme(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$uri = new Url($urlString);
		
		$this->assertSame('//' . $urlString, $uri->getAbsolute());
	}
	
	public function testSetSchemeValidChanged(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$uri = new Url('http://' . $urlString);
		$uri->setScheme('    HTTPS    ');
		
		$this->assertSame('https://' . $urlString, strval($uri));
	}
	
	public function testSetSchemeValidSame(): void
	{
		$urlString = 'http://hostname:9090';
		$uri = new Url($urlString);
		$uri->setScheme('http');
		
		$this->assertSame($urlString, strval($uri));
	}
	
	public function testSetSchemeInvalid(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Unsupported URI scheme "foo"');
		
		$uri = new Url('http://hostname:9090/path?arg=value#fragment');
		$uri->setScheme('foo');
	}
	
	public function testSetAuthValidSame(): void
	{
		$urlString = 'http://foo:bar@hostname:9090';
		$uri = new Url($urlString);
		$uri->setAuth('foo', 'bar');
		
		$this->assertSame($urlString, strval($uri));
	}
	
	public function testSetAuthValidChanged(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$uri = new Url('http://foo:bar@' . $urlString);
		$uri->setAuth('baz', 'qux');
		
		$this->assertSame('http://baz:qux@' . $urlString, strval($uri));
	}
	
	public function testSetAuthInvalidUsername(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid auth credentials');
		
		$uri = new Url('http://hostname:9090/path?arg=value#fragment');
		$uri->setAuth('foo:bar');
	}
	
	public function testSetAuthMissingUsername(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Cannot have a password without a username');
		
		$uri = new Url('http://hostname:9090/path?arg=value#fragment');
		$uri->setAuth('', 'foo');
	}
	
	public function testSetHostValidSame(): void
	{
		$urlString = 'http://hostname:9090';
		$uri = new Url($urlString);
		$uri->setHost('//HOSTNAME/');
		
		$this->assertSame($urlString, strval($uri));
	}
	
	public function testSetHostValidChanged(): void
	{
		$resource = '/path?arg=value#fragment';
		$uri = new Url('http://hostname:9090' . $resource);
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
		
		$uri = new Url('http://hostname:9090/path?arg=value#fragment');
		$uri->setHost($invalidHost);
	}
	
	public function testSetPortValidSame(): void
	{
		$urlString = 'http://hostname:9090/';
		$uri = new Url($urlString);
		$uri->setPort(9090);
		
		$this->assertSame($urlString, strval($uri));
	}
	
	public function testSetPortValidChanged(): void
	{
		$uri = new Url('http://hostname:9090/');
		$uri->setPort(90);
		
		$this->assertSame('http://hostname:90/', strval($uri));
	}
	
	public function testClearPort(): void
	{
		$uri = new Url('http://hostname:9090/');
		$uri->setPort(null);
		
		$this->assertSame('http://hostname/', strval($uri));
	}
	
	public function testSetPortInvalid(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid port number "-1"');
		
		$uri = new Url('http://hostname:9090/');
		$uri->setPort(-1);
	}
	
	public function testSetPathValid(): void
	{
		$urlString = 'http://hostname:9090';
		$uri = new Url($urlString);
		$uri->setPath('/////foo/////');
		
		$this->assertSame($urlString . '/foo/', strval($uri));
	}
	
	public function testSetPathInvalid(): void
	{
		$path = '/path?contains=other_bits';
		
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid path "' . $path . '"');
		
		(new Url('http://hostname:9090/'))->setPath($path);
	}
	
	public function testSetQueryValid(): void
	{
		$urlString = 'http://hostname:9090/';
		$uri = new Url($urlString);
		
		$uri->setQuery('');
		$this->assertSame($urlString, strval($uri));
		
		$uri->setQuery('foo=bar');
		$this->assertSame($urlString . '?foo=bar', strval($uri));
		
		$uri->setQuery('?foo=bar');
		$this->assertSame($urlString . '?foo=bar', strval($uri));
	}
	
	public function testSetQueryInvalid(): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage('Invalid query "foo=bar#baz"');
		
		$uri = new Url('hostname:9090');
		$uri->setQuery('foo=bar#baz');
	}
	
	public function testSetQueryParametersValid(): void
	{
		$urlString = 'http://hostname:9090/';
		$queryParameters = ['foo' => ['bar' => 'baz']];
		
		$uri = new Url($urlString);
		$uri->setQueryParameters($queryParameters);
		
		$this->assertSame($queryParameters, $uri->getQueryParameters()->getAll());
		$this->assertSame($urlString . '?foo%5Bbar%5D=baz', strval($uri));
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
		
		$uri = new Url('http://hostname:9090/');
		$uri->setQueryParameters(['foo' => $invalidQueryParameter]);
	}
	
	public function testSetQueryParameterValid(): void
	{
		$uri = new Url('http://hostname:9090?foo=bar');
		$uri->setQueryParameter('foo', ['bar' => 'baz']);
		$uri->setQueryParameter(['qux', 'quux', 0], 'quuux');
		
		$this->assertSame(
			[
				'foo' => ['bar' => 'baz'],
				'qux' => ['quux' => [0 => 'quuux']],
			],
			$uri->getQueryParameters()->getAll()
		);
		$this->assertSame('?foo%5Bbar%5D=baz&qux%5Bquux%5D%5B0%5D=quuux', $uri->getRelative());
	}
	
	/** @dataProvider invalidQueryParameterDataset */
	public function testSetQueryParameterInvalid($invalidQueryParameter): void
	{
		$this->expectException(UriException::class);
		$this->expectExceptionMessage(
			'Invalid query parameter "' . gettype($invalidQueryParameter) . '" for key "foo"'
		);
		
		$uri = new Url('http://hostname:9090/?foo=bar');
		$uri->setQueryParameter('foo', $invalidQueryParameter);
	}
	
	public function testSetFragment(): void
	{
		$urlString = 'http://hostname:9090/?foo=bar';
		$uri = new Url($urlString);
		$uri->setFragment('baz');
		
		$this->assertSame($urlString . '#baz', strval($uri));
	}
}
