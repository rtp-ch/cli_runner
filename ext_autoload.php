<?php

$extensionPath = t3lib_extMgm::extPath('cli_runner');
$extensionClassesPath = $extensionPath . 'Classes/';

require_once $extensionClassesPath . 'Cli/Options.php';
require_once $extensionClassesPath . 'Cli/Runner.php';
require_once $extensionClassesPath . 'Command/Arguments.php';
require_once $extensionClassesPath . 'Command/Export.php';
require_once $extensionClassesPath . 'Command/Method.php';
require_once $extensionClassesPath . 'Command/Qlass.php';
require_once $extensionClassesPath . 'Service/Compatibility.php';
require_once $extensionClassesPath . 'Service/Frontend.php';
require_once $extensionClassesPath . 'Utility/Console.php';
require_once $extensionClassesPath . 'Utility/File.php';
require_once $extensionClassesPath . 'Utility/Typo3.php';