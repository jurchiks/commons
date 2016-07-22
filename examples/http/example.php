<?php
use js\tools\commons\exceptions\UriException;
use js\tools\commons\http\Uri;

require __DIR__ . '/../autoloader.php';

$uri = new Uri('http://j:s@foo.bar:8080/whoa/what/?ran=dom&que[]=ry#blargh');

var_dump('protocol=' . $uri->getScheme());
var_dump('username=' . $uri->getUsername());
var_dump('password=' . $uri->getPassword());
var_dump('host=' . $uri->getHost());
var_dump('port=' . $uri->getPort());
var_dump('path=' . $uri->getPath());
var_dump('query=' . $uri->getQuery());
var_dump('query params=', $uri->getQueryParameters());
var_dump('fragment=' . $uri->getFragment());

echo $uri->getRelative(), PHP_EOL;
echo $uri->setScheme('https'), PHP_EOL;
echo $uri->setAuth(''), PHP_EOL;
echo $uri->setAuth('j2'), PHP_EOL;
echo $uri->setPort(80), PHP_EOL;
echo $uri->setPort(7070), PHP_EOL;
echo $uri->setHost('foobar.baz'), PHP_EOL;
echo $uri->setPath('/wow'), PHP_EOL;
echo $uri->setQueryParameter('ran', 't'), PHP_EOL;

$uri2 = new Uri('/foo/bar');

var_dump('protocol=' . $uri2->getScheme());
var_dump('username=' . $uri2->getUsername());
var_dump('password=' . $uri2->getPassword());
var_dump('host=' . $uri2->getHost());
var_dump('port=' . $uri2->getPort());
var_dump('path=' . $uri2->getPath());
var_dump('query=' . $uri2->getQuery());
var_dump('query params=', $uri2->getQueryParameters());
var_dump('fragment=' . $uri2->getFragment());

$tryCatch = function (string $func, ...$params) use (&$uri2)
{
	try
	{
		$uri2->$func(...$params);
	}
	catch (UriException $e)
	{
		echo $e->getMessage(), PHP_EOL;
	}
};

$tryCatch('getAbsolute'); // this is a relative URL, no absolute parts are defined, so this will throw an exception
$tryCatch('setHost', ''); // can't have an empty host; if you want to get the relative part of a URL, use getRelative()
$tryCatch('setHost', 'domain/tld'); // slashes in host not allowed (leading/trailing slashes are ok, they're trimmed)
$tryCatch('setAuth', '', 'nonemptypassword'); // can't have password without username
$tryCatch('setPort', -123); // can't have a negative port
// no idea what would make a path or a query string invalid...
$tryCatch('setQueryParameter', 'foo', false); // only int/float/string/bool and array[int|float|string|bool] are allowed
// http_build_query() behavior:
// nulls and resources are completely ignored, and only public properties of objects are serialized
// as such, it doesn't make sense to allow objects, as they most likely aren't intended to be put into a URL
