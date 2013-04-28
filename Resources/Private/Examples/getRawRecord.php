<?php

$fromTable = 'pages';
$selectFields = '*';
$uid = 1;

$GLOBALS['_cli_arguments'] = array(
    $fromTable,
    $uid,
    $selectFields
);

$GLOBALS['TYPO3_DB']->debugOutput = true;
$GLOBALS['_cli_export'] =& $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;

