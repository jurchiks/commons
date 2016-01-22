<?php
// a replacement autoloader for Composer's PSR-4 autoloader
spl_autoload_register(function ($name)
{
	$name = str_replace('\\', '/', $name);
	$name = str_replace('js/tools/commons', '', $name);
	$path = __DIR__ . '/../src/' . $name . '.php';
	
	if (file_exists($path))
	{
		require $path;
	}
});
