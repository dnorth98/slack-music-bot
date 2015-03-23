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

function request($app,$slackUser,$text) 
{
	$returnArray = array("text" => "");

  	$app['monolog']->addDebug('REQUEST routine: ' . $text );

	if (strpos($text,"by") === false)
	{
		$returnArray['text'] = $slackUser . ', requests need to be of the form SONG by ARTIST. Try again.';
	} else
	{
		// write the command to the DB
		$status = writeToDB($app,$slackUser,"request",$text);
		if ($status)
		{
			$returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve submitted your request for ' . $text . ' to the music controller';
		} else
		{
			$returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to submit your request for ' . $text . ' to the music controller';
		}
	}

	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('REQUEST routine returning: ' . $returnJSON );

	return $returnJSON;
}

function play($app,$slackUser,$text) 
{
	$returnArray = array("text" => "");

  	$app['monolog']->addDebug('PLAY routine: ' . $text );
	//echo "PLAY routine " . $text . "\n";

	// write the command to the DB
	$status = writeToDB($app,$slackUser,"play","");
	if ($status)
	{
		$returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve asked the music controller to play some funky beats';
	} else
	{
		$returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to ask the music controller to play some funky beats';
	}

	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('PLAY routine returning: ' . $returnJSON );

	return $returnJSON;
}

function skip($app,$slackUser,$text) 
{
	$returnArray = array("text" => "");

  	$app['monolog']->addDebug('SKIP routine: ' . $text );
	//echo "NEXT routine " . $text . "\n";

	// write the command to the DB
	$status = writeToDB($app,$slackUser,"next","");
	if ($status)
	{
		$returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve asked the music controller to skip to the next track';
	} else
	{
		$returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to ask the music controller to skip to the next track';
	}

	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('SKIP routine returning: ' . $returnJSON );

	return $returnJSON;
}

function stop($app,$slackUser,$text) 
{
	$returnArray = array("text" => "");

  	$app['monolog']->addDebug('STOP routine: ' . $text );

	// write the command to the DB
	$status = writeToDB($app,$slackUser,"stop","");
	if ($status)
	{
		$returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve asked the music controller to stop the beats';
	} else
	{
		$returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to ask the music controller to stop the beats';
	}

	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('PAUSE routine returning: ' . $returnJSON );

	return $returnJSON;
}

function nowplaying($app,$slackUser,$text) 
{
	$returnArray = array("text" => "");

  	$app['monolog']->addDebug('NOWPLAYING routine: ' . $text );

	// write the command to the DB
	$status = writeToDB($app,$slackUser,"nowplaying","");
	if ($status)
	{
		$returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve asked the music controller to report back what is playing';
	} else
	{
		$returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to ask the music controller what is playing';
	}

	$returnJSON = json_encode($returnArray);	

  	$app['monolog']->addDebug('NOWPLAYING routine returning: ' . $returnJSON );

	return $returnJSON;
}

function help($app,$slackUser,$text)
{
	$returnArray = array("text" => "");

  	$app['monolog']->addDebug('HELP routine: ' . $text );

	$helpText = 'You can ask me the following:\n';
	$helpText = $helpText . '*play* - play whatever is queued up\n';
	$helpText = $helpText . '*stop* - stop the funky beats\n';
	$helpText = $helpText . '*next* - skip to the next track\n';
	$helpText = $helpText . '*nowplaying* - report back what is currently playing\n';
	$helpText = $helpText . '*request* <songname> by <artist> - request a song be added to the playlist\n';
	$helpText = $helpText . 'eg: heydj request danger zone by kenny loggins\n';

	$returnArray['text'] = $helpText;

	$returnJSON = json_encode($returnArray,JSON_HEX_AMP|JSON_HEX_APOS|JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
	// slack wants a non-escaped \n to render multi lines...
	$returnJSON = str_replace('\\\\n','\\n',$returnJSON);

  	$app['monolog']->addDebug('HELP routine returning: ' . $returnJSON );

	return $returnJSON;
}

function writeToDB($app,$slackUser,$cmd,$textArg)
{
	$retVal = true;

  	$app['monolog']->addDebug('writeToDB: ' . $cmd . ' for ' . $slackUser );
	// INSERT INTO dj_actions(dj_command,dj_arg,slack_user,retrieved) values ("A","B","C",FALSE)

	$st = $app['pdo']->prepare('INSERT INTO dj_actions (dj_command,dj_arg,slack_user,retrieved) values (:dj_cmd,:dj_arg,:slack_user,FALSE)');
	
	$retVal = $st->execute(array(
			'dj_cmd' => $cmd,
			'dj_arg' => $textArg,
			'slack_user' => $slackUser	
			)
	);

	return $retVal;
}

function validateToken($inToken,$validToken)
{

	return ($inToken == $validToken);
}

function userAllowed($app,$currentUser,$allowedUsers)
{
	$app['monolog']->addDebug('userAllowed: Checking if ' . $currentUser . ' is allowed.');

	$allowed =  false;
	$allowedUsersArray = explode(",",$allowedUsers);

	if ($allowedUsersArray[0] == "*")
	{
		$allowed = true;
	} else
	{
		foreach ($allowedUsersArray as $allowedUser)
		{
			if (strcasecmp($allowedUser, $currentUser) == 0)
			{
				$app['monolog']->addDebug('$currentUser IS allowed.');
				$allowed = true;
			}
		}
	}

	return $allowed;
}

$app->post('/', function(Request $request) use($app) {
	$app['monolog']->addDebug('In handler for root context.');

	$returnJSON = "{}";
	$returnArray = array();

	$inToken = $request->get('token');
  	$validToken = getenv('SLACK_TOKEN');
  	$configSlackWord = getenv('SLACK_WORD');
	$validUsers = getenv('SLACK_ALLOWED_USERS');

	// TESTING
	//$validToken="foo";
	//$configSlackWord = "heydj";

	if (validateToken($inToken,$validToken))
	{
  		$app['monolog']->addDebug('Slack token is ok - message is for us');

		$word = $request->get('trigger_word');

		if ($word == $configSlackWord)
		{
  			$app['monolog']->addDebug('Trigger word received: ' . $word);

			$text = $request->get('text');
			$slackUser = $request->get('user_name');

			// the text contains our keyword, the command and the argument
			$wordsArray = explode(" ",$text);
			$commandWord = $wordsArray[1];

			// get the argument
			$remainingTextArray = array_slice($wordsArray,2);
			$remainingText = implode(" ",$remainingTextArray);	

  			$app['monolog']->addDebug('Triggered with cmd: ' . $commandWord . ' Arg: ' . $remainingText);
			//echo "Triggered with command: " . $commandWord . " Argument: " . $remainingText . "\n";
			if (userAllowed($app,$slackUser,$validUsers))
			{
				$returnJSON = $commandWord($app,$slackUser,$remainingText);
			} else
			{
				$msgStr = "Sorry, you're not good enough to request tunes!";
				$returnArray = array("text" => $msgStr);
				$returnJSON = json_encode($returnArray,JSON_HEX_AMP|JSON_HEX_APOS|JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
			}
		}
	}

  return $returnJSON;
});

$app->run();
?>
