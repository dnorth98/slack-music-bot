function skip($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $app['monolog']->addDebug('HEYDJ SKIP routine: ' . $slackUser . ' ' . $text );
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

        $app['monolog']->addDebug('HEYDJ SKIP routine returning: ' . $returnJSON );

        return $returnJSON;
}
