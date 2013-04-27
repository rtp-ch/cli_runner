<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Registers the CLI script
$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['runner']
    = array('EXT:' . $_EXTKEY . '/cli/class.rtp_cli.php', '_CLI_rtp');

