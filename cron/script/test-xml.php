<?php

// Callback functions
function start_tag($parser, $element_name, $element_attrs)
{
    callback_dump( __FUNCTION__, func_get_args ( ) );
}

function end_tag($parser, $element_name)
{
    callback_dump( __FUNCTION__, func_get_args ( ) );
}

function character_data($parser, $data)
{
    callback_dump( __FUNCTION__, func_get_args ( ) );
}

// Main program
//$xmlParser = xml_parser_create("ISO-8859-1");
//$xmlParser = xml_parser_create("UTF-8");
$xmlParser = xml_parser_create();
xml_set_element_handler($xmlParser, 'start_tag', 'end_tag');
xml_set_character_data_handler($xmlParser, 'character_data');
//xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING, "ISO-8859-1");

$fp = fopen('PrixCarburants_instantane.xml', 'r')
    or die ("Cannot open xml source file");


while ($data = fread($fp, 4096)) {
  xml_parse($xmlParser, $data, feof($fp)) or
    die(sprintf('XML ERROR: %s at line %d',
        xml_error_string(xml_get_error_code($xmlParser)),
        xml_get_current_line_number($xmlParser)));
}


xml_parser_free($xmlParser);
echo "\nTerminé\n";

// User functions
function callback_dump( $func_name, $func_args )
{
    echo "\n" . $func_name . "(";
//    print_r($func_args);
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
