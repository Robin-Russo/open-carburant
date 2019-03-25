<?php
require("commun.php");
require("stack.php");
require("pdv_model.php");
require("xml_callback.php");


setConfiguration();
logMessage("Début de traitement");

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_errno) {
    logMessage(sprintf("Erreur connexion MySQL (%s): %s",$mysqli->connect_errno, $mysqli->connect_error));
    exit(1);
}

/*
if (!$result = $mysqli->query("TRUNCATE TABLE xml_mirror ")) {
    logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
    exit(1);
}
*/

$aInput = inputFileList();

foreach($aInput as $sInputJson) {
    inputRequest($sInputJson);
}

$mysqli->close();
logMessage("Fin de traitement");
exit(0);

////// Functions

function inputRequest($sRequestFile)
{
    global $nCountPdv;
    global $nCountInsert;
    global $nCountUpdate;

    $sInputPath = BASE_PATH . "/input/";

    $aRequest = json_decode(file_get_contents($sInputPath.$sRequestFile), true);
    $sUrl = $aRequest['url']??"";
    $sZipFile = $aRequest['file']??"";
    logMessage("Traitement zip: ".$sZipFile. " url: " .  $sUrl );

    if (!empty($sZipFile) && file_exists($sInputPath.$sZipFile) ) {
        $zip = zip_open($sInputPath.$sZipFile);

        if ($zip) {

        	while ($zip_entry = zip_read($zip)) {
                $aXmlFile = [
                        'zip' => $zip,
                        'zip_entry' => $zip_entry,
                        'filename' => zip_entry_name($zip_entry),
                        'size' => zip_entry_filesize($zip_entry)
                    ];

                logMessage( "xml : " . $aXmlFile['filename'] . " size : " . $aXmlFile['size'] );
                $nCountPdv=0;
                $nCountInsert=0;
                $nCountUpdate=0;

                parseFile($aXmlFile);
                logMessage("PDV traités: $nCountPdv Insertions: $nCountInsert MAJ: $nCountUpdate");
        	}

            zip_close($zip);
        }
    }
}

function parseFile($aXmlFile)
{

    $xmlParser = xml_parser_create();
    xml_set_element_handler($xmlParser, 'start_tag', 'end_tag');
    xml_set_character_data_handler($xmlParser, 'character_data');

    if ( zip_entry_open($aXmlFile['zip'], $aXmlFile['zip_entry'], "r") ) {

        do {
            $sData = zip_entry_read($aXmlFile['zip_entry'], 4096);

            if ( xml_parse($xmlParser, $sData, empty($sData)) == 0 ) {

                $sErrorString = xml_error_string(xml_get_error_code($xmlParser));
                $LineNumber = xml_get_current_line_number($xmlParser);
                logMessage(sprintf('Erreur XML: %s ligne %d', $sErrorString, $LineNumber));
            }

        } while (!empty($sData));

        zip_entry_close($aXmlFile['zip_entry']);
    }

    xml_parser_free($xmlParser);
}


function inputFileList()
{
    $sInputPath = BASE_PATH . "/input/";
    $aReturn = array();

    $aFile = scandir($sInputPath);

    foreach ($aFile as $sFile) {
        if (
            ($sFile!='.') &&
            ($sFile!='..') &&
            (substr($sFile,0,4)=='flux') &&
            (substr($sFile,-5)=='.json')
            ) {

            $aStat = stat($sInputPath.$sFile);
            $aReturn[] = $sFile;
            $aTime[] = $aStat['ctime'];
        }
    }

    array_multisort($aTime, $aReturn );

    return($aReturn);
}

function diff_compare_pdv($aPdv)
{
    global $nCountPdv;
    global $nCountInsert;
    global $nCountUpdate;
    global $mysqli;

//    $nCountInsert = 0;
//    $nCountUpdate = 0;

    $nCountPdv++;

    //Prepare array $aPdv
    $aPdv['adresse'] = $mysqli->real_escape_string($aPdv['adresse']);
    $aPdv['ville'] = $mysqli->real_escape_string($aPdv['ville']);
    $aPdv['horaire'] = json_encode($aPdv['horaire']);
    $aPdv['services'] = $mysqli->real_escape_string(json_encode($aPdv['services'],JSON_HEX_APOS));
    $aPdv['prix'] = json_encode($aPdv['prix']);
    $aPdv['rupture'] = json_encode($aPdv['rupture']);
    $aPdv['fermeture'] = json_encode($aPdv['fermeture']);

    $sQuery = sprintf("SELECT * FROM xml_mirror WHERE id='%s' ", $aPdv['id']);
    if (!$result = $mysqli->query($sQuery)) {
        logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
        exit(1);
    }

    if ($result->num_rows == 0) {
        $nCountInsert++;

        $sQueryTemplate = "INSERT INTO `xml_mirror` (
            `id`,
            `latitude`,
            `longitude`,
            `cp`,
            `route_autoroute`,
            `adresse`,
            `ville`,
            `horaire`,
            `automate_24_24`,
            `services`,
            `prix`,
            `rupture`,
            `fermeture`
            )
            VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) ";

        $sQuery = sprintf($sQueryTemplate,
            $aPdv['id'],
            $aPdv['latitude'],
            $aPdv['longitude'],
            $aPdv['cp'],
            $aPdv['route_autoroute'],
            $aPdv['adresse'],
            $aPdv['ville'],
            $aPdv['horaire'],
            $aPdv['automate_24_24'],
            $aPdv['services'],
            $aPdv['prix'],
            $aPdv['rupture'],
            $aPdv['fermeture']
            );

            if (!$result = $mysqli->query($sQuery)) {
                logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
                echo("query: ".$sQuery);
                exit(1);
            }

            ajoutePdv($aPdv);

    } else {
        $aPdvMirror = $result->fetch_assoc();

        $aPdvMirror['adresse'] = $mysqli->real_escape_string($aPdvMirror['adresse']);
        $aPdvMirror['ville'] = $mysqli->real_escape_string($aPdvMirror['ville']);
        $aPdvMirror['services'] = $mysqli->real_escape_string($aPdvMirror['services']);


        $aDiff = array_diff_assoc($aPdv, $aPdvMirror);
        if (count($aDiff)>0) {
            $nCountUpdate++;

//            echo "\nDifferences\n";        print_r($aDiff);
            $aFields=array();
            foreach ($aDiff as $key => $value) {
                $aFields[]=$key;
            }
            majPdv($aPdv,$aFields);

        }

    }

}
