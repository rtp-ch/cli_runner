<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Registers the CLI script
$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['cli_runner'] = array(
    'EXT:cli_runner/Classes/Cli/Runner.php',
    '_CLI_lowlevel'
);