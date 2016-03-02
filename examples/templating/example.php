<?php
use js\tools\commons\templating\Engine;
use js\tools\commons\templating\Template;

require __DIR__ . '/../autoloader.php';

// with the engine:
$engine = new Engine(__DIR__ . '/templates');

echo $engine->render('login');
echo PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL;
echo $engine->render('register');

// without the engine:
echo PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL;
echo (new Template(__DIR__ . '/templates/noengine.phtml'))->render();

// as you can see, using the template allows you to use shorter syntax, but requires a reference to the Engine
