<?php

global $_cli_arguments;

$selectFields = 'uid,pid,title';
$fromTable = 'pages';
$whereClause = '1 = 1' . $GLOBALS['TSFE']->sys_page->enableFields($fromTable);
$groupBy = 'pid';
$orderBy = 'title';
$limit = 20;
$uidIndexField = 'uid';

$GLOBALS['TYPO3_DB']->debugOutput = true;

$_cli_arguments = array(
    $selectFields,
    $fromTable,
    $whereClause,
    $groupBy,
    $orderBy,
    $limit,
    $uidIndexField
);

