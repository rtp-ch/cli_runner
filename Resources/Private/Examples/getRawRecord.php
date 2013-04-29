<?php

$fromTable = 'pages';
$selectFields = 'uid,pid,title';
$uid = 1;

$GLOBALS['_cli_arguments'] = array(
    $fromTable,
    $uid,
    $selectFields
);

$GLOBALS['TYPO3_DB']->debugOutput = true;

$GLOBALS['_cli_method'] = 'getRawRecord';
$GLOBALS['_cli_class'] =& $GLOBALS['TSFE']->sys_page;
$GLOBALS['_cli_debug'] =& $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;

