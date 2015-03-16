<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Guzzle\Http\Client;


$app = new Silex\Application();

$app['debug'] = true;

// Register the postgres service
$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
  array(
    'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/').';host='.$dbopts["host"],
    'pdo.port' => $dbopts["port"],
    'pdo.username' => $dbopts["user"],
    'pdo.password' => $dbopts["pass"]
  )
);

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

function getValuesFromDB($app,$limit)
{
	$app['monolog']->addDebug('getValuesFromDB LIMIT '. $limit);
	// returns an array
	$retArray = array();

	$st = $app['pdo']->prepare('with dj_actions as (update dj_actions set retrieved=true where retrieved=false returning id,dj_command,dj_arg,slack_user) select id,dj_command,dj_arg,slack_user from dj_actions order by id asc;');
	$st->execute();

	$names = array();
	while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
		$item = array(
			"id" => $row['id'],
			"command" => $row['dj_command'],
			"arg" => $row['dj_arg'],
			"user" => $row['slack_user']
			);
		array_push($retArray,$item);
		$app['monolog']->addDebug('Row read ID:' . $id . ' ' . $row['dj_command']);
	}

	return $retArray;
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

	// TESTING
	//$validToken="foo";
	//$configSlackWord = "heydj";

	if (validateToken($inToken,$validToken))
	{
		echo "token validated\n";
  		$app['monolog']->addDebug('token is ok - message is for us');

		$limit = $request->get('limit');

		// query the DB and return the values in json
		$dbValsArray = getValuesFromDB($app,$limit);

		$returnJSON = json_encode($dbValsArray,JSON_HEX_AMP|JSON_HEX_APOS|JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
	}

  return $returnJSON;
});

$app->run();
?>
