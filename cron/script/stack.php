<?php

$aStack = array();

function stack_push($aElement)
{
    global $aStack;

    array_push( $aStack, $aElement);
//    print_r($aStack);
}

function stack_pop()
{
    global $aStack;

    return( array_pop($aStack) );
}


function stack_last_get( $attr )
{
    global $aStack;

    $return = false;

    $nLast = count($aStack)-1;
    if ( ($nLast>=0) && ( isset($aStack[$nLast][$attr]) ) ) {
        $return = $aStack[$nLast][$attr];
    }

    return( $return );
}

function stack_last_set( $attr, $value)
{
    global $aStack;

    $nLast = count($aStack)-1;
    if ($nLast>=0) {
        $aStack[$nLast][$attr] = $value;
    }

}
