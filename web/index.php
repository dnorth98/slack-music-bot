<?php

require('../vendor/autoload.php');
require_once '../shooker/shooker.php';

function ktotemps($k) {
	$obj = new stdClass;
	$obj->celsius = $k-273.15;
	$obj->fahrenheit = ($obj->celsius*9/5)+32;
	return $obj;
}

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

$shkr = new Shooker('JJMHMqG7bVry0XRKuoFKb1qH');
	 
$testTrigger = $shkr->addTrigger("weather");
$testTrigger->addAction(function($paramString, $user, $channel){
	$json = file_get_contents('http://api.openweathermap.org/data/2.5/weather?q='.$paramString);
	$obj = json_decode($json);
	$curTemp = ktotemps($obj->main->temp);
	return "Currently *".round($curTemp->fahrenheit)."F (".round($curTemp->celsius)."C)* and *".$obj->weather[0]->description."*.";
});

$shkr->listen();

?>
