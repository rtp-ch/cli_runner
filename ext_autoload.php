<?php

if(version_compare(TYPO3_version, '6.0.0', '>=')) {

    $extensionPath = t3lib_extMgm::extPath('cli_runner');
    $extensionClassesPath = $extensionPath . 'Classes/';

    return array(
        'RTP\CliRunner\Cli\Options' => $extensionClassesPath . 'Cli/Options.php',
        'RTP\CliRunner\Cli\Runner' => $extensionClassesPath . 'Cli/Runner.php',
        'RTP\CliRunner\Command\Arguments' => $extensionClassesPath . 'Command/Arguments.php',
        'RTP\CliRunner\Command\Debug' => $extensionClassesPath . 'Command/Debug.php',
        'RTP\CliRunner\Command\Method' => $extensionClassesPath . 'Command/Method.php',
        'RTP\CliRunner\Command\Qlass' => $extensionClassesPath . 'Command/Qlass.php',
        'RTP\CliRunner\Service\Compatibility' => $extensionClassesPath . 'Service/Compatibility.php',
        'RTP\CliRunner\Service\Frontend' => $extensionClassesPath . 'Service/Frontend.php',
        'RTP\CliRunner\Utility\Console' => $extensionClassesPath . 'Utility/Console.php',
        'RTP\CliRunner\Utility\File' => $extensionClassesPath . 'Utility/File.php',
        'RTP\CliRunner\Utility\Typo3' => $extensionClassesPath . 'Utility/Typo3.php'
    );
}
