<?php

function nextup($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $app['monolog']->addDebug('HEYDJ NEXTUP routine: ' . $slackUser . ' ' . $text );

        // write the command to the DB
        $status = writeToDB($app,$slackUser,"nowplaying","");
        if ($status)
        {
                $returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve asked the music controller to tell us what\'s spinning next';
        } else
        {
                $returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to ask the music controller to tell us what\s up next';
        }

        $returnJSON = json_encode($returnArray);

        $app['monolog']->addDebug('HEYDJ NEXTUP routine returning: ' . $returnJSON );

        return $returnJSON;
}

