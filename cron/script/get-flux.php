<?php
require("commun.php");


setConfiguration();
logMessage("Début de traitement");

if ($argc==2) {
    $sUrl = '';
    switch ( strtolower($argv[1]) ) {
        case 'instantane':
            $sUrl = "https://donnees.roulez-eco.fr/opendata/instantane";
            break;
        case 'jour':
            $sUrl = "https://donnees.roulez-eco.fr/opendata/jour";
            break;
        case 'annee':
            $sUrl = "https://donnees.roulez-eco.fr/opendata/annee";
            break;
        default:
            logMessage("paramètre incorrect:" . $argv[1] );
            break;
    }
    if ( !empty($sUrl) ) {
        getFlux($sUrl);
    }

} else {
    logMessage("Erreur parametres: " . implode(' ', $argv) );
}

logMessage("Fin de traitement");


function getFlux($sUrl)
{
    $sInputPath = BASE_PATH . "/input/";

    $nNumRequest = getNumRequest();
    $sFile = "flux" . sprintf("%04s", $nNumRequest) . ".zip";
    $sFileJson = "flux" . sprintf("%04s", $nNumRequest) . ".json";

    logMessage("Téléchargement $sUrl => $sFile");

    $fp = fopen($sInputPath.$sFile, 'w');

    $ch = curl_init($sUrl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_exec($ch);
    $curl_errno = curl_errno($ch);
    if ($curl_errno > 0) {
        logMessage("Erreur cURL ($curl_errno): ".curl_error($ch));
    }

    curl_close($ch);
    fclose($fp);

    $fp = fopen($sInputPath.$sFileJson, 'w');
    $sJson = json_encode([
        'url' => $sUrl,
        'file' => $sFile
        ]);
    fwrite($fp,$sJson);
    fclose($fp);

}


function getNumRequest()
{
    $sInputPath = BASE_PATH . "/input/";

    $nRand=0;
    do {
        $nRand = random_int( 1 , 9999 );
        $sFile = "flux" . sprintf("%04s", $nRand) . ".zip";
    }
    while( file_exists($sInputPath . $sFile) );

    if (! touch($sInputPath . $sFile) ) {
        logMessage("impossible de créer " . $sInputPath . $sFile);
    }

    return($nRand);
}
