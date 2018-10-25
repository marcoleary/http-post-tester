<?php

require_once 'vendor/autoload.php';

date_default_timezone_set('Europe/London');

$guzzle = new GuzzleHttp\Client();

$log = new Monolog\Logger(__CLASS__);
$log->pushHandler(new Monolog\Handler\StreamHandler(sprintf('%s/../logs/results_%s.log', __DIR__, date('YmdHis')), Monolog\Logger::WARNING));
$log->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::DEBUG));

$objPostTester = new HttpPostTester\HttpPostTester($guzzle, $log);

$objPostTester->init();
