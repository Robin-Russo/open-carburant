<?php
require("commun.php");

logMessage("Début de traitement");
setConfiguration();

cleanDirectory( BASE_PATH."/backup/", 2, ['gz'] );

dumpDatabase(DB_DATABASE);

targzDirectory("/cron/script");
targzDirectory("/html");

$sDirSummary = "Répertoire backup : %s fichiers. Espace disque : %s Ko";
logMessage( sprintf( $sDirSummary, getNbFiles( BASE_PATH."/backup/" ), getDirSize( BASE_PATH."/backup/" ) ) );

logMessage("Fin de traitement");
exit(0);


function dumpDatabase($sDatabase)
{
	$outReturn = true;

	$sOutFile = "db-opencarburant.".date("Ymd").".sql.gz";

    $sCmdTemplate = "mysqldump --defaults-extra-file=/cron/script/mysql-credentials.cnf --add-drop-table --default-character-set=UTF8 --extended-insert ".$sDatabase." | gzip --stdout > ".BASE_PATH."/backup/%s";
	$sCommande = sprintf($sCmdTemplate, $sOutFile );
    exec ( $sCommande, $aRetour );

	if( count($aRetour) > 0 ) {
        logMessage("Erreur pendant execution commande:".$sCommande);
        logMessage("Message erreur:");
        logMessage($aRetour[0]);

		$outReturn = FALSE;
	}

	return($outReturn);
}

function targzDirectory($sDirectory)
{
	$outReturn = true;

    $sDirectory = substr($sDirectory, 1);
    $sTargzFile = BASE_PATH."/backup/backup-".str_replace('/', '-', $sDirectory)."-".date("Ymd").".tar.gz";

	$sCommande = sprintf("tar -zcf %s -C / %s", $sTargzFile, $sDirectory );
    exec ( $sCommande, $aRetour );

	if( count($aRetour) > 0 ) {
        logMessage("Erreur pendant execution commande:".$sCommande);
        logMessage("Message erreur:");
        logMessage($aRetour[0]);

		$outReturn = FALSE;
	}

	return($outReturn);
}
