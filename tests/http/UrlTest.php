<?php
namespace js\tools\commons\tests\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\UrlException;
use js\tools\commons\http\Url;
use PHPUnit\Framework\TestCase;
use stdClass;

class UrlTest extends TestCase
{
	public function invalidUrlDataset(): iterable
	{
		yield ['///foo'];
		yield ['http:///domain.tld'];
		yield ['http://:80'];
		yield ['http://user@:80'];
		yield ['http://domain.tld:90000'];
	}
	
	/** @dataProvider invalidUrlDataset */
	public function testInvalidUrls(string $invalidUrl): void
	{
		$this->expectException(UrlException::class);
		
		new Url($invalidUrl);
	}
	
	public function testAbsoluteUrl(): void
	{
		$resource = '/path?arg=value#fragment';
		$urlString = 'http://username:password@hostname:9090' . $resource;
		$url = new Url($urlString);
		$this->assertSame('http', $url->getScheme());
		$this->assertSame('username', $url->getUsername());
		$this->assertSame('password', $url->getPassword());
		$this->assertSame('hostname', $url->getHost());
		$this->assertSame(9090, $url->getPort());
		$this->assertSame('/path', $url->getPath());
		$this->assertSame('?arg=value', $url->getQuery());
		$this->assertSame(['arg' => 'value'], $url->getQueryParameters()->getAll());
		$this->assertSame('#fragment', $url->getFragment());
		$this->assertSame($urlString, $url->getAbsolute());
		$this->assertSame($resource, $url->getRelative());
		$this->assertSame($urlString, $url->get());
		$this->assertSame($urlString, strval($url));
		$this->assertTrue($url->isAbsolute());
	}
	
	public function testRelativeUrl(): void
	{
		$resource = '/path?arg=value#fragment';
		$url = new Url($resource);
		$this->assertSame('', $url->getScheme());
		$this->assertSame('', $url->getUsername());
		$this->assertSame('', $url->getPassword());
		$this->assertSame('', $url->getHost());
		$this->assertNull($url->getPort());
		$this->assertSame('/path', $url->getPath());
		$this->assertSame('?arg=value', $url->getQuery());
		$this->assertSame(['arg' => 'value'], $url->getQueryParameters()->getAll());
		$this->assertSame('#fragment', $url->getFragment());
		$this->assertSame($resource, $url->getRelative());
		$this->assertSame($resource, $url->get());
		$this->assertSame($resource, strval($url));
		$this->assertFalse($url->isAbsolute());
	}
	
	public function testSourceOnly(): void
	{
		$urlString = 'http://username:password@hostname:9090';
		$url = new Url($urlString);
		$this->assertSame('http', $url->getScheme());
		$this->assertSame('username', $url->getUsername());
		$this->assertSame('password', $url->getPassword());
		$this->assertSame('hostname', $url->getHost());
		$this->assertSame(9090, $url->getPort());
		$this->assertSame('', $url->getPath());
		$this->assertSame('', $url->getQuery());
		$this->assertSame([], $url->getQueryParameters()->getAll());
		$this->assertSame('', $url->getFragment());
		$this->assertSame($urlString, $url->getAbsolute());
		$this->assertSame('', $url->getRelative());
		$this->assertSame($urlString, $url->get());
		$this->assertSame($urlString, strval($url));
		$this->assertTrue($url->isAbsolute());
	}
	
	public function createFromGlobalsDataset(): iterable
	{
		yield ['domain.tld', '/foo?bar=baz', 'on', 'https://domain.tld/foo?bar=baz'];
		yield ['domain.tld', '/', null, 'http://domain.tld/'];
	}
	
	/** @dataProvider createFromGlobalsDataset */
	public function testCreateFromGlobalsValid(string $host, string $requestUrl, $https, $expectedUrl): void
	{
		$_SERVER['HTTP_HOST'] = $host;
		$_SERVER['REQUEST_URI'] = $requestUrl;
		$_SERVER['HTTPS'] = $https;
		
		$url = Url::createFromGlobals();
		
		$this->assertSame($expectedUrl, $url->getAbsolute());
		
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
		$url = new Url('http://username:password@hostname:9090/path?arg=value#fragment');
		$copy = $url->copy();
		
		$this->assertInstanceOf(Url::class, $copy);
		$this->assertNotSame($url, $copy);
		$this->assertSame($url->getAbsolute(), $copy->getAbsolute());
	}
	
	public function testQueryEncoding(): void
	{
		$url = new Url('?arg=value with spaces');
		
		$this->assertSame('?arg=value+with+spaces', $url->getQuery());
		$this->assertSame('?arg=value%20with%20spaces', $url->getQuery(true));
		$this->assertSame(['arg' => 'value with spaces'], $url->getQueryParameters()->getAll());
	}
	
	public function testInvalidAbsoluteUrl(): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Cannot make an absolute URL without host');
		
		(new Url('/path?arg=value#fragment'))->getAbsolute();
	}
	
	public function testWithoutScheme(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$url = new Url($urlString);
		
		$this->assertSame('//' . $urlString, $url->getAbsolute());
	}
	
	public function testSetSchemeValidChanged(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$url = new Url('http://' . $urlString);
		$url->setScheme('    HTTPS    ');
		
		$this->assertSame('https://' . $urlString, strval($url));
	}
	
	public function testSetSchemeValidSame(): void
	{
		$urlString = 'http://hostname:9090';
		$url = new Url($urlString);
		$url->setScheme('http');
		
		$this->assertSame($urlString, strval($url));
	}
	
	public function testSetAuthValidSame(): void
	{
		$urlString = 'http://foo:bar@hostname:9090';
		$url = new Url($urlString);
		$url->setAuth('foo', 'bar');
		
		$this->assertSame($urlString, strval($url));
	}
	
	public function testSetAuthValidChanged(): void
	{
		$urlString = 'hostname:9090/path?arg=value#fragment';
		$url = new Url('http://foo:bar@' . $urlString);
		$url->setAuth('baz', 'qux');
		
		$this->assertSame('http://baz:qux@' . $urlString, strval($url));
	}
	
	public function testSetAuthInvalidUsername(): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Invalid auth credentials');
		
		$url = new Url('http://hostname:9090/path?arg=value#fragment');
		$url->setAuth('foo:bar');
	}
	
	public function testSetAuthMissingUsername(): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Cannot have a password without a username');
		
		$url = new Url('http://hostname:9090/path?arg=value#fragment');
		$url->setAuth('', 'foo');
	}
	
	public function testSetHostValidSame(): void
	{
		$urlString = 'http://hostname:9090';
		$url = new Url($urlString);
		$url->setHost('//HOSTNAME/');
		
		$this->assertSame($urlString, strval($url));
	}
	
	public function testSetHostValidChanged(): void
	{
		$resource = '/path?arg=value#fragment';
		$url = new Url('http://hostname:9090' . $resource);
		$url->setHost('host.name');
		
		$this->assertSame('http://host.name:9090' . $resource, strval($url));
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
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Invalid host "' . $invalidHost . '"');
		
		$url = new Url('http://hostname:9090/path?arg=value#fragment');
		$url->setHost($invalidHost);
	}
	
	public function testSetPortValidSame(): void
	{
		$urlString = 'http://hostname:9090/';
		$url = new Url($urlString);
		$url->setPort(9090);
		
		$this->assertSame($urlString, strval($url));
	}
	
	public function testSetPortValidChanged(): void
	{
		$url = new Url('http://hostname:9090/');
		$url->setPort(90);
		
		$this->assertSame('http://hostname:90/', strval($url));
	}
	
	public function testClearPort(): void
	{
		$url = new Url('http://hostname:9090/');
		$url->setPort(null);
		
		$this->assertSame('http://hostname/', strval($url));
	}
	
	public function testSetPortInvalid(): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Invalid port number "-1"');
		
		$url = new Url('http://hostname:9090/');
		$url->setPort(-1);
	}
	
	public function testSetPathValid(): void
	{
		$urlString = 'http://hostname:9090';
		$url = new Url($urlString);
		$url->setPath('/////foo/////');
		
		$this->assertSame($urlString . '/foo/', strval($url));
	}
	
	public function testSetPathInvalid(): void
	{
		$path = '/path?contains=other_bits';
		
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Invalid path "' . $path . '"');
		
		(new Url('http://hostname:9090/'))->setPath($path);
	}
	
	public function testSetQueryValid(): void
	{
		$urlString = 'http://hostname:9090/';
		$url = new Url($urlString);
		
		$url->setQuery('');
		$this->assertSame($urlString, strval($url));
		
		$url->setQuery('foo=bar');
		$this->assertSame($urlString . '?foo=bar', strval($url));
		
		$url->setQuery('?foo=bar');
		$this->assertSame($urlString . '?foo=bar', strval($url));
	}
	
	public function testSetQueryInvalid(): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage('Invalid query "foo=bar#baz"');
		
		$url = new Url('hostname:9090');
		$url->setQuery('foo=bar#baz');
	}
	
	public function testGetQueryParameter(): void
	{
		$url = new Url('/foo');
		$url->setQueryParameter('bar', 'baz');
		
		$this->assertSame('baz', $url->getQueryParameter('bar'));
	}
	
	public function testSetQueryParametersValid(): void
	{
		$urlString = 'http://hostname:9090/';
		$queryParameters = ['foo' => ['bar' => 'baz']];
		
		$url = new Url($urlString);
		$url->setQueryParameters($queryParameters);
		
		$this->assertSame($queryParameters, $url->getQueryParameters()->getAll());
		$this->assertSame($urlString . '?foo%5Bbar%5D=baz', strval($url));
	}
	
	public function invalidQueryParameterDataset(): iterable
	{
		yield [null];
		yield [new stdClass()];
	}
	
	/** @dataProvider invalidQueryParameterDataset */
	public function testSetQueryParametersInvalid($invalidQueryParameter): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage(
			'Invalid query parameter "' . gettype($invalidQueryParameter) . '" for key "foo"'
		);
		
		$url = new Url('http://hostname:9090/');
		$url->setQueryParameters(['foo' => $invalidQueryParameter]);
	}
	
	public function testSetQueryParameterValid(): void
	{
		$url = new Url('http://hostname:9090?foo=bar');
		$url->setQueryParameter('foo', ['bar' => 'baz']);
		$url->setQueryParameter(['qux', 'quux', 0], 'quuux');
		
		$this->assertSame(
			[
				'foo' => ['bar' => 'baz'],
				'qux' => ['quux' => [0 => 'quuux']],
			],
			$url->getQueryParameters()->getAll()
		);
		$this->assertSame('?foo%5Bbar%5D=baz&qux%5Bquux%5D%5B0%5D=quuux', $url->getRelative());
	}
	
	/** @dataProvider invalidQueryParameterDataset */
	public function testSetQueryParameterInvalid($invalidQueryParameter): void
	{
		$this->expectException(UrlException::class);
		$this->expectExceptionMessage(
			'Invalid query parameter "' . gettype($invalidQueryParameter) . '" for key "foo"'
		);
		
		$url = new Url('http://hostname:9090/?foo=bar');
		$url->setQueryParameter('foo', $invalidQueryParameter);
	}
	
	public function testSetFragment(): void
	{
		$urlString = 'http://hostname:9090/?foo=bar';
		$url = new Url($urlString);
		$url->setFragment('baz');
		
		$this->assertSame($urlString . '#baz', strval($url));
	}
}
