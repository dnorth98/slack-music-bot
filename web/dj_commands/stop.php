<?php

function stop($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $app['monolog']->addDebug('HEYDJ STOP routine: ' . $slackUser . ' ' . $text );

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

        $app['monolog']->addDebug('HEYDJ PAUSE routine returning: ' . $returnJSON );

        return $returnJSON;
}

