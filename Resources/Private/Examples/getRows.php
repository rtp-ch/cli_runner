<?php

$selectFields = 'uid,pid,title';
$fromTable = 'pages';
$whereClause = '1 = 1' . $GLOBALS['TSFE']->sys_page->enableFields($fromTable);
$groupBy = 'pid';
$orderBy = 'title';
$limit = 20;
$uidIndexField = 'uid';

$GLOBALS['TYPO3_DB']->debugOutput = true;

$GLOBALS['_cli_arguments'] = array(
    $selectFields,
    $fromTable,
    $whereClause,
    $groupBy,
    $orderBy,
    $limit,
    $uidIndexField
);

$GLOBALS['_cli_method'] = 'exec_SELECTgetRows';
$GLOBALS['_cli_class'] =& $GLOBALS['TYPO3_DB'];
$GLOBALS['_cli_debug'] =& $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;

