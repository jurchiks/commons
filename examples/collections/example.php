<?php
use js\tools\commons\collections\MutableList;
use js\tools\commons\collections\MutableMap;

require __DIR__ . '/../autoloader.php';

#######################################################################################

$counter = 0;
$failed = 0;

$test = function (string $message, $data, $expectedValue) use (&$counter, &$failed)
{
	$counter++;
	
	echo $counter, ') ', $message, ': ';
	
	if ($data === $expectedValue)
	{
		echo 'pass';
	}
	else
	{
		$failed++;
		echo 'fail', PHP_EOL;
		echo 'expected: ';
		var_dump($expectedValue);
		echo PHP_EOL;
		echo 'actual: ';
		var_dump($data);
		echo PHP_EOL;
	}
	
	echo PHP_EOL;
};

$testResults = function () use (&$counter, &$failed)
{
	echo 'Status: ', ($counter - $failed), '/', $counter, PHP_EOL;
};

#######################################################################################

$collection = new MutableList(range(1, 100));

$collection->map(
		function ($value)
		{
			return $value / 2;
		}
	)
	->filter(
		function ($value, $key)
		{
			return $value > 25;
		}
	);

$test('collection size #1', $collection->size(), 50);

$groups = $collection->group(
	function ($value, $key)
	{
		return (is_int($value) ? 'ints' : 'floats');
	}
);
$data = $groups->get();

$test('collection size #2', $groups->size(), 2);
$test('number of ints in group "ints"', count($data['ints']), 25);
$test('group "floats" contains float 25.5', in_array(25.5, $data['floats']), true);

$groups->flatten();

$test('collection size #3', $groups->size(), 50);
$test('collection contains 25.5', $groups->containsValue(25.5), true);

$groups->filter(
	function ($value, $key)
	{
		return is_int($value);
	}
);

$test('collection size #4', $groups->size(), 25);

foreach ($groups as $key => $value)
{
	echo $key, ' => ', $value, PHP_EOL;
}

$test('collection contains a float', $groups->containsValue(26.5), false);
$test('collection contains key 24', $groups->containsKey(24), true);

$entry = $groups->getKey(26);

$test('key is found', $entry->isFound(), true);
$test('key is 0', $entry->get(), 0);
$test('group does not contain 25', $groups->getValue(25)->getOrElse('nope'), 'nope');
$test('get all keys for value 26', $groups->getKeys(26), [0]);
$test(
	'get first value with index > 5', $groups->findValue(
	function ($value, $key)
	{
		return ($key > 5);
	}
)->get(), 32
);
$test(
	'get last key of any value that is divisible by 3', $groups->findKey(
	function ($value, $key)
	{
		return ($value % 3 === 0);
	}, false
)->get(), 22
);
$test(
	'get all values that are divisible by 3', $groups->findValues(
	function ($value, $key)
	{
		return ($value % 3 === 0);
	}
), [27, 30, 33, 36, 39, 42, 45, 48]
);
$test(
	'get last two keys of values divisible by 3', $groups->findKeys(
	function ($value, $key)
	{
		return ($value % 3 === 0);
	}, -2
), [19, 22]
);

$test(
	'ArrayList::append()', (new MutableList([1, 2]))->append(3)->get(), [1, 2, 3]
);
$test(
	'ArrayList::prepend()', (new MutableList([1, 2]))->prepend(3)->get(), [3, 1, 2]
);
$test(
	'ArrayList::remove()', (new MutableList([1, 2, 3]))->remove(3)->get(), [1, 2]
);
$test(
	'ArrayMap::set()', (new MutableMap(['foo' => 'bar']))->set('bar', 'baz')->get(), ['foo' => 'bar', 'bar' => 'baz']
);
$test(
	'ArrayMap::unset()', (new MutableMap(['foo' => 'bar', 'bar' => 'baz']))->unset('bar')->get(), ['foo' => 'bar']
);
$test(
	'ArrayMap::remove()', (new MutableList(['foo' => 'bar', 'baz' => 'bar']))->remove('bar')->get(), []
);

$testResults();
