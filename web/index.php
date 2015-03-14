<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Guzzle\Http\Client;


$app = new Silex\Application();

$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

function pause($app,$text) 
{
	$returnArray = array("text" => "");
  	$app['monolog']->addDebug('PAUSE routine: ' . $text );

	// TODO post the command to the queue

	$returnArray['text'] = 'DOOD!  You gave me ' . $text;
	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('PAUSE routine returning: ' . $returnJSON );

	return $returnJSON;
}

function play($app,$text) 
{
	$returnArray = array("text" => "");
  	$app['monolog']->addDebug('PLAY routine: ' . $text );

	// TODO post the command to the queue

	$returnArray['text'] = 'DOOD!  You gave me ' . $text;
	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('PLAY routine returning: ' . $returnJSON );

	return $returnJSON;
}

function validateToken($inToken,$validToken)
{

	return ($inToken == $validToken);
}

$app->post('/', function(Request $request) use($app) {
	$app['monolog']->addDebug('In handler for root context.');

	$returnJSON = "{}";
	$inToken = $request->get('token');
  	$validToken = getenv('SLACK_TOKEN');

	if (validateToken($inToken,$validToken))
	{
  		$app['monolog']->addDebug('Slack token is ok - message is for us');

		$word = $request->get('trigger_word');
		$text = $request->get('text');

  		$app['monolog']->addDebug('Trigger word received: ' . $word);

		$returnJSON = $word($app,$text);
	}

  return $returnJSON;
});

$app->run();
?>
