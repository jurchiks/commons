<?php
use js\tools\commons\logging\CustomLogger;
use js\tools\commons\logging\FileLogger;

require __DIR__ . '/../autoloader.php';

$fileLogger = new FileLogger(__DIR__);

$fileLogger->log(FileLogger::DEBUG, 'testing testing %s %s %s', 1, 2, 3);



$logHandler = function (string $message, int $level)
{
	// you can implement any kind of logging by using this handler, e.g. logging to database, sending output to FTP,
	// using some external APIs or whatever else you want
	echo CustomLogger::getLevelName($level), ' received: ', $message, PHP_EOL;
};
$customLogger = new CustomLogger($logHandler);

$customLogger->log(CustomLogger::INFO, 'testing again');
