<?php

$selectFields = 'uid,pid,title';
$fromTable = 'pages';
$whereClause = '1 = 1' . $GLOBALS['TSFE']->sys_page->enableFields($fromTable);
$groupBy = 'pid';
$orderBy = 'title';
$limit = 20;
$uidIndexField = 'uid';

$GLOBALS['TYPO3_DB']->debugOutput = true;

$args = array(
    $selectFields,
    $fromTable,
    $whereClause,
    $groupBy,
    $orderBy,
    $limit,
    $uidIndexField
);