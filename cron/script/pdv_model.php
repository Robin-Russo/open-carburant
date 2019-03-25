<?php


function majPdv($aPdv,$aFields)
{

//    echo "aPdv",print_r($aPdv); echo PHP_EOL;
//    echo "aFields",print_r($aFields); echo PHP_EOL;

    $aFieldPdv = [ 'latitude', 'longitude', 'cp', 'adresse', 'ville'];
    $lUpdate = false;
    $lUpdatePrix = false;
    foreach ($aFields as $sField) {
        if (in_array($sField, $aFieldPdv)) {
            $lUpdate = true;
        }
        if ($sField=='prix') {
            $lUpdatePrix = true;
        }
    }

    if ($lUpdate) {
        updatePdv($aPdv);
    }

    if ($lUpdatePrix) {
//        updatePdvPrix($aPdv);
        $aPrix = json_decode($aPdv['prix'],true);
//        print_r($aPrix);

        foreach ($aPrix as $key => $aRow) {
            $sCid = substr($key,0,1);

            $aPdvPrix = [
                'pdvid' => $aPdv['id'],
                'cid' => $sCid,
                'cnom' => $aRow['nom'],
                'maj' => $aRow['maj'],
                'timestamp' => maj2timestamp($aRow['maj']),
                'prix' => $aRow['valeur']
                ];

            if ( ! existPdvPrix($aPdvPrix) ) {
                insertPdvPrix($aPdvPrix);
            }
        }
    }

}



function ajoutePdv($aPdv)
{

//    echo "aPdv",print_r($aPdv); echo PHP_EOL;

    insertPdv($aPdv);

    $aPrix = json_decode($aPdv['prix'],true);
//    print_r($aPrix);
    foreach ($aPrix as $key => $aRow) {
        $sCid = substr($key,0,1);

        $aPdvPrix = [
            'pdvid' => $aPdv['id'],
            'cid' => $sCid,
            'cnom' => $aRow['nom'],
            'maj' => $aRow['maj'],
            'timestamp' => maj2timestamp($aRow['maj']),
            'prix' => $aRow['valeur']
            ];

        insertPdvPrix($aPdvPrix);
    }


}

function maj2timestamp($sMaj)
{
    $sYear  = substr($sMaj,0,4);
    $sMonth = substr($sMaj,5,2);
    $sDay   = substr($sMaj,8,2);
    $sHour  = substr($sMaj,11,2);
    $sMin   = substr($sMaj,14,2);
    $sSec   = substr($sMaj,17,2);
    $nTimestamp = mktime($sHour, $sMin, $sSec, $sMonth, $sDay, $sYear);

    return($nTimestamp);
}


function insertPdv($aData)
{
    global $mysqli;

    $sQueryTemplate = "INSERT INTO `pdv` (
        `pdvid`,
        `latitude`,
        `longitude`,
        `cp`,
        `adresse`,
        `ville`
        )
        VALUES ( '%s', '%s', '%s', '%s', '%s', '%s' ) ";

    $sQuery = sprintf($sQueryTemplate,
        $aData['id'],
        $aData['latitude'],
        $aData['longitude'],
        $aData['cp'],
        $aData['adresse'],
        $aData['ville']
        );

    if (!$result = $mysqli->query($sQuery)) {
        logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
        echo("query: ".$sQuery);
        exit(1);
    }

}



function updatePdv($aData)
{
    global $mysqli;

    $sQueryTemplate = " UPDATE `pdv`
                        SET `latitude`='%s', `longitude`='%s', `cp`='%s', `adresse`='%s', `ville`='%s'
                        WHERE `pdvid` = '%s' ";

    $sQuery = sprintf($sQueryTemplate,
        $aData['latitude'],
        $aData['longitude'],
        $aData['cp'],
        $aData['adresse'],
        $aData['ville'],
        $aData['id'],
        );

    if (!$result = $mysqli->query($sQuery)) {
        logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
        echo("query: ".$sQuery);
        exit(1);
    }

}


function insertPdvPrix($aData)
{
    global $mysqli;

    $sQueryTemplate = "INSERT INTO `pdv_prix` (
        `pdvid`,
        `cid`,
        `cnom`,
        `maj`,
        `timestamp`,
        `prix`
        )
        VALUES ( '%s', '%s', '%s', '%s', '%s', %s ) ";

    $sQuery = sprintf($sQueryTemplate,
        $aData['pdvid'],
        $aData['cid'],
        $aData['cnom'],
        $aData['maj'],
        $aData['timestamp'],
        $aData['prix']
        );

    if (!$result = $mysqli->query($sQuery)) {
        logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
        echo("query: ".$sQuery);
        exit(1);
    }

}


function existPdvPrix($aData)
{
    global $mysqli;

    $lReturn = false;

    $sQueryTemplate = " SELECT `pdvid` FROM `pdv_prix`
                        WHERE `pdvid` = '%s' AND `cid` = '%s' AND `maj`='%s' ";

    $sQuery = sprintf($sQueryTemplate,
        $aData['pdvid'],
        $aData['cid'],
        $aData['maj'],
        );

    if (!$result = $mysqli->query($sQuery)) {
        logMessage(sprintf("Erreur MySQL (%s): %s", $mysqli->errno, $mysqli->error));
        echo("query: ".$sQuery);
        exit(1);
    }

    if ($result->num_rows == 0) {
        $lReturn = true;
    }

    return($lReturn);
}
