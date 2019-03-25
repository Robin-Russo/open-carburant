<?php

function logMessage($sMessage)
{
    global $argv;

    // 20190323 00:43:15 get-flux.php downloading https://donnees.roulez-eco.fr/opendata/jour
    $sLogTemplate = "%s, %s, %s\n";
    echo sprintf($sLogTemplate, date("Ymd H:i:s"), basename($argv[0]), $sMessage);

}

function setConfiguration()
{
    define('BASE_PATH', '/cron');

    define('DB_HOST', "dbserver" );
    define('DB_DATABASE', "opencarburant" );
    define('DB_USER', "opencarburant_user" );
    define('DB_PASSWORD', "pass*9876" );

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
