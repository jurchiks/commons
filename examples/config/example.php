<?php
require __DIR__ . '/../autoloader.php';

use js\tools\commons\config\Config;

$data = [
	'dotted.property' => true,
	'app' => [
		'token' => '#$@5dfgzwe5z3weff',
		'db' => [
			'host' => 'localhost',
			'port' => '3306',
		],
	],
];
$config = Config::loadFromArray($data);

echo 'test=', $config->getBool('dotted.property'), PHP_EOL;
echo 'token=', $config->getString('app.token'), PHP_EOL;
echo 'port=', $config->getInt('app.db.port'), PHP_EOL;
