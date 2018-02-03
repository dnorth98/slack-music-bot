<?php

function request($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $cleanedText = clean($text);

        $app['monolog']->addDebug('HEYDJ REQUEST routine: ' . $slackUser . ' ' . $text );

        if (strpos($text,"by") === false)
        {
                $returnArray['text'] = $slackUser . ', requests need to be of the form SONG by ARTIST. Try again.';
        } else
        {
                // write the command to the DB
                $status = writeToDB($app,$slackUser,"request",$cleanedText);
                if ($status)
                {
                        $returnArray['text'] = 'OK, ' . $slackUser . ' I\'ve submitted your request for ' . $text . ' to the music controller';
                } else
                {
                        $returnArray['text'] = 'I\'m sorry ' . $slackUser . ', I was unable to submit your request for ' . $text . ' to the music controller';
                }
        }

        $returnJSON = json_encode($returnArray);

        $app['monolog']->addDebug('HEYDJ REQUEST routine returning: ' . $returnJSON );

        return $returnJSON;
}
