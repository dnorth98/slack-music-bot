<?php

function help($app,$slackUser,$text)
{
        $returnArray = array("text" => "");

        $app['monolog']->addDebug('HELP routine: ' . $text );

        $helpText = 'You can ask me the following:\n';
        $helpText = $helpText . '*play* - play whatever is queued up\n';
        $helpText = $helpText . '*stop* - stop the funky beats\n';
        $helpText = $helpText . '*skip* - skip to the next track\n';
        $helpText = $helpText . '*nowplaying* - report back what is currently playing\n';
        $helpText = $helpText . '*nextup* - report back what is coming up\n';
        $helpText = $helpText . '*request* <songname> by <artist> - request a song be added to the playlist\n'
;
        $helpText = $helpText . 'eg: heydj request danger zone by kenny loggins\n';

        $returnArray['text'] = $helpText;

        $returnJSON = json_encode($returnArray,JSON_HEX_AMP|JSON_HEX_APOS|JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
        // slack wants a non-escaped \n to render multi lines...
        $returnJSON = str_replace('\\\\n','\\n',$returnJSON);

        $app['monolog']->addDebug('HELP routine returning: ' . $returnJSON );

        return $returnJSON;
}

