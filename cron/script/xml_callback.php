<?php

// Callback functions
function start_tag($parser, $element_name, $element_attrs)
{

    if ($element_name != 'PDV_LISTE') {

        $path = stack_last_get( 'path' );
        if ($path !== false) {
            $sCurrentPath = $path . '/' . $element_name;
        } else {
            $sCurrentPath = $element_name;
        }

        $aElement = [
            'path' => $sCurrentPath,
            'tag' => $element_name,
            'attr' => $element_attrs,
            'data' => ''
            ];

        stack_push($aElement);
    }

}

function end_tag($parser, $element_name)
{

if ($element_name != 'PDV_LISTE') {

        $aElement = stack_pop();

        switch ($aElement['path']) {
            case 'PDV/VILLE':
                stack_last_set( 'ville', $aElement['data']);
                break;
            case 'PDV/ADRESSE':
                stack_last_set( 'adresse', $aElement['data']);
                break;
            case 'PDV/HORAIRES/JOUR/HORAIRE':
                stack_last_set( 'ouverture', $aElement['attr']['OUVERTURE']);
                stack_last_set( 'fermeture', $aElement['attr']['FERMETURE']);
                break;
            case 'PDV/HORAIRES/JOUR':
                $horaire = stack_last_get( 'horaire' );
                if ( ! is_array($horaire) ) {
                    $horaire = array();
                }

                $id = $aElement['attr']['ID'];
                $horaire[$id] = [
                        'jour' => $aElement['attr']['NOM'],
                        'ferme' => $aElement['attr']['FERME'],
                        'ouverture' => $aElement['ouverture']??"",
                        'fermeture' => $aElement['fermeture']??""
                    ];

                stack_last_set( 'horaire', $horaire);
                break;

            case 'PDV/HORAIRES':
                stack_last_set( 'horaire', $aElement['horaire']);
                stack_last_set( 'automate_24_24', $aElement['attr']['AUTOMATE-24-24']);
                break;

            case 'PDV/SERVICES/SERVICE':
                $services = stack_last_get( 'services' );
                if ( ! is_array($services) ) {
                    $services = array();
                }

                $services[] = $aElement['data'];
                stack_last_set( 'services', $services);
                break;

            case 'PDV/SERVICES':
                $services = $aElement['services']??[];
                stack_last_set( 'services', $services);
                break;

            case 'PDV/PRIX':
                if (isset($aElement['attr']['ID'])) {
                    $prix = stack_last_get( 'prix' );
                    if ( ! is_array($prix) ) {
                        $prix = array();
                    }

                    $id = $aElement['attr']['ID'].'-'.$aElement['attr']['MAJ'];
                    $prix[$id] = [
                        'nom' => $aElement['attr']['NOM'],
                        'maj' => $aElement['attr']['MAJ'],
                        'valeur' => $aElement['attr']['VALEUR']
                        ];
                    stack_last_set( 'prix', $prix);
                }
                break;

            case 'PDV/RUPTURE':
                if (isset($aElement['attr']['ID'])) {
                    $rupture = stack_last_get( 'rupture' );
                    if ( ! is_array($rupture) ) {
                        $rupture = array();
                    }

                    $id = $aElement['attr']['ID'];
                    $rupture[$id] = [
                        'nom' => $aElement['attr']['NOM'],
                        'debut' => $aElement['attr']['DEBUT'],
                        'fin' => $aElement['attr']['FIN']
                        ];
                    stack_last_set( 'rupture', $rupture);
                }
                break;

            case 'PDV/FERMETURE':
                $aFermeture = [
                    'type' => $aElement['attr']['TYPE']??"",
                    'debut' => $aElement['attr']['DEBUT']??"",
                    'fin' => $aElement['attr']['FIN']??""
                ];
                stack_last_set( 'fermeture_pdv', $aFermeture);
                break;

            case 'PDV':
                $aPdv = [
                    'id' => $aElement['attr']['ID'],
                    'latitude' => $aElement['attr']['LATITUDE'],
                    'longitude' => $aElement['attr']['LONGITUDE'],
                    'cp' => $aElement['attr']['CP'],
                    'route_autoroute' => $aElement['attr']['POP'],
                    'adresse' => $aElement['adresse'],
                    'ville' => $aElement['ville'],
                    'horaire' => $aElement['horaire']??[],
                    'automate_24_24' => $aElement['automate_24_24']??"",
                    'services' => $aElement['services'],
                    'prix' => $aElement['prix']??[],
                    'rupture' => $aElement['rupture']??[],
                    'fermeture' => $aElement['fermeture']??[]
                ];
                diff_compare_pdv($aPdv);
                break;

            default:
                echo "********************** Tag non géré **********************\n";
                echo "\nelement:\n"; print_r($aElement);
                break;
        }
    }
}

function character_data($parser, $data)
{

    if (trim($data) != '') {
        $sData = stack_last_get( 'data' ) . $data;
        stack_last_set( 'data', $sData);
    }

}
