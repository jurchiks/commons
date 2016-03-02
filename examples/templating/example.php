<?php
use js\tools\commons\templating\Engine;
use js\tools\commons\templating\Extension;
use js\tools\commons\templating\Template;

require __DIR__ . '/../autoloader.php';

// without the engine:
echo (new Template(__DIR__ . '/templates/noengine.phtml'))->render();

// with the engine:
$engine = new Engine(__DIR__ . '/templates');

class CaseExtension implements Extension
{
	public function lc(string $value)
	{
		return mb_strtolower($value, 'UTF-8');
	}
	
	public function uc(string $value)
	{
		return mb_strtoupper($value, 'UTF-8');
	}
}

$engine->addFunction('lolcase', function (string $value)
{
	$values = str_split($value, 1);
	
	for ($i = 0, $length = count($values); $i < $length; $i++)
	{
		$values[$i] = (($i % 2 === 0)
			? strtolower($values[$i])
			: strtoupper($values[$i]));
	}
	
	return implode('', $values);
});
$engine->addExtension(new CaseExtension());

echo PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL;
echo $engine->render('login');
echo PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL;
echo $engine->render('register');

// as you can see, using the Engine allows you to use shorter syntax
// and adds the ability to implement your own template functions,
// but requires a reference to the Engine object
