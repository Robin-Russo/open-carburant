<?php

function logMessage($sMessage)
{
    global $argv;

    // 20190323 00:43:15 get-flux.php downloading https://donnees.roulez-eco.fr/opendata/jour
    $sLogTemplate = "\n%s, %s, %s";
    echo sprintf($sLogTemplate, date("Ymd H:i:s"), basename($argv[0]), $sMessage);

}

function setConfiguration()
{
    date_default_timezone_set('Europe/Paris');

    define('BASE_PATH', '/cron');

    define('DB_HOST', "dbserver" );
    define('DB_DATABASE', "opencarburant" );
    define('DB_USER', "opencarburant_user" );
    define('DB_PASSWORD', "pass*9876" );

}

function cleanDirectory( $inDirectory, $inJour, $aExtension )
{

	$nNow = time();

	$aDir = scandir($inDirectory);
	foreach( $aDir as $sFile) {
        foreach ($aExtension as $sExtension) {
            $sExtension = '.' . $sExtension;
            $nLen = strlen($sExtension) * -1;

            if ( (substr($sFile, $nLen) == $sExtension ) ) {
    			$nTime = filectime( $inDirectory . $sFile );
    			$nbJours = ($nNow - $nTime) / 86400;

    			if ($nbJours >= $inJour ) {
    				unlink($inDirectory . $sFile);
    				logMessage( sprintf('cleanDirectory("%s") : Fichier %s effacé', $inDirectory, $sFile) );
    			}
    		}

        }
	}
}

function getDirSize( $inPath )
{
	$aReturn = array();

	exec( "du -k ".$inPath." | awk '{print $1}'", $aReturn );
	$nSizeDir = intval( $aReturn[0] );

	return($nSizeDir);
}


function getNbFiles( $inPath )
{
	$aReturn = array();

	exec( "ls ".$inPath. " | wc -l", $aReturn );
	$nFiles = intval($aReturn[0]);

	return($nFiles);
}

function callback_dump( $func_name, $func_args )
{
    echo "\n" . $func_name . "(";
    foreach ($func_args as $param_number => $param_val) {

        switch (gettype($param_val)) {
            case 'resource':
                echo "\n\t$param_number: resource";
                break;
            case 'string':
                echo sprintf("\n\t$param_number: \"%s\"",$param_val);
                break;
            case 'array':
                echo sprintf("\n\t$param_number: array(",$param_val);
                foreach ($param_val as $attr_name => $attr_val) {
                    echo sprintf("\n\t\t$attr_name=\"%s\"", $attr_val);
                }
                echo "\n\t)";
                break;
            default:
                break;
        }
    }

    echo "\n)";
}
