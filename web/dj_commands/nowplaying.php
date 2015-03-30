<?php

function nowplaying($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $app['monolog']->addDebug('HEYDJ NOWPLAYING routine: ' . $slackUser . ' ' . $text );

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

        $app['monolog']->addDebug('HEYDJ NOWPLAYING routine returning: ' . $returnJSON );

        return $returnJSON;
}

