<?php
use js\tools\commons\traits\DataWriter;
use js\tools\commons\traits\StaticDataWriter;

require __DIR__ . '/../autoloader.php';

class Foo
{
	use DataWriter;
}

class Bar
{
	use StaticDataWriter;
}

$foo = new Foo();

$foo->set('a.b.c', 'foo');

var_dump($foo->getArray('a.b'));
var_dump($foo->get('a.b.c'));

Bar::set('a.b.c', 'bar');

var_dump(Bar::getArray('a.b'));
var_dump(Bar::get('a.b.c'));
