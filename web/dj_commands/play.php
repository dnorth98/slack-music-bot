<?php

function play($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $app['monolog']->addDebug('HEYDJ PLAY routine: ' . $slackUser . ' ' . $text );
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

        $app['monolog']->addDebug('HEYDJ PLAY routine returning: ' . $returnJSON );

        return $returnJSON;
}
