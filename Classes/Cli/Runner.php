<?php
namespace RTP\CliRunner\Cli;

use BadMethodCallException;
use Exception;
use RTP\CliRunner\Utility\Method;
use RTP\CliRunner\Utility\Console;
use RTP\CliRunner\Utility\File;
use RTP\CliRunner\Service\Frontend;
use RTP\CliRunner\Service\Compatibility;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

if(version_compare(TYPO3_version, '6.0.0', '<')) {

    $extensionPath = \t3lib_extMgm::extPath('cli_runner');
    $extensionClassesPath = $extensionPath . 'Classes/';

    require_once $extensionClassesPath . 'Cli/Options.php';
    require_once $extensionClassesPath . 'Cli/Setup.php';
    require_once $extensionClassesPath . 'Command/Arguments.php';
    require_once $extensionClassesPath . 'Command/Debug.php';
    require_once $extensionClassesPath . 'Command/Method.php';
    require_once $extensionClassesPath . 'Command/Qlass.php';
    require_once $extensionClassesPath . 'Scripts/Extension.php';
    require_once $extensionClassesPath . 'Service/Compatibility.php';
    require_once $extensionClassesPath . 'Service/Frontend.php';
    require_once $extensionClassesPath . 'Utility/Console.php';
    require_once $extensionClassesPath . 'Utility/File.php';
    require_once $extensionClassesPath . 'Utility/Method.php';
    require_once $extensionClassesPath . 'Utility/Qlass.php';
    require_once $extensionClassesPath . 'Utility/Typo3.php';
}

/**
 * Class Cli
 * @package RTP\CliRunner
 */
class Runner
{

    /**
     * @var \RTP\CliRunner\Command\Method
     */
    private $method;

    /**
     * @var \RTP\CliRunner\Command\Qlass
     */
    private $qlass;

    /**
     * @var \RTP\CliRunner\Command\Arguments
     */
    private $arguments;

    /**
     * @var \RTP\CliRunner\Command\Debug
     */
    private $debug;

    /**
     * @var \RTP\CliRunner\Cli\Setup
     */
    private $setup;

    /**
     * @var \RTP\CliRunner\Cli\Options
     */
    private $options;

    /**
     * @param $options
     * @throws BadMethodCallException
     */
    public function main($options)
    {
        $this->options = Compatibility::makeInstance('RTP\\CliRunner\\Cli\\Options', $options);

        // Prints the help message if requested
        if ($this->options->has('help')) {
            console::help();
            exit;
        }

        /**
         * [1] Simulate a frontend environment
         * ===================================
         * Create an instance of tslib_fe
         */

         Frontend::simulate($this->page(), $this->hasCache());

        /**
         * [2] Include a(ny) PHP file. Do what you like...
         * ===============================================
         */
        try {
            if ($this->options->has('file')) {
                if (File::isValid($this->options->get('file'))) {
                    File::load($this->options->get('file'));

                } else {
                    $msg  = 'Invalid file type "' . $this->options->get('file') . '"! ';
                    $msg .= 'File must have one of the following extensions: ' . implode(',', File::getValidTypes());
                    throw new BadMethodCallException($msg, 1366569266);
                }
            }

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        /**
         * [3] Run setup code
         * ==================
         */
        try {
            $this->setup = Compatibility::makeInstance('RTP\\CliRunner\\Cli\\Setup', $this->options);
            $this->setup->set();
            $this->setup->run();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        // [4] Set the arguments to pass to the method
        // ===========================================
        try {
            $this->arguments = Compatibility::makeInstance('RTP\\CliRunner\\Command\\Arguments', $this->options);
            $this->arguments->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        /**
         * [5] Set the class to instantiate
         * ================================
         */
        try {
            $this->qlass = Compatibility::makeInstance('RTP\\CliRunner\\Command\\Qlass', $this->options);
            $this->qlass->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        // [6] Set the method or function to invoke
        // ========================================
        try {
            $this->method = Compatibility::makeInstance('RTP\\CliRunner\\Command\\Method', $this->options);
            $this->method->set();
            Method::isValid($this->method->get(), $this->qlass->get());

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        // [7] Execute the method
        // ======================
        try {
            $result = Method::execute($this->method->get(), $this->arguments->get(), $this->qlass->get());

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message(
                $msg,
                $this->arguments->get(),
                Method::getSignature($this->method->get(), $this->qlass->get()),
                Method::getDocumentation($this->method->get(), $this->qlass->get())
            );
        }


        // [8] Process any debug settings
        // ==============================
        try {
            $this->debug = Compatibility::makeInstance(
                'RTP\\CliRunner\\Command\\Debug',
                $this->options,
                $this->qlass
            );
            $this->debug->set($result);

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message(
                $msg,
                $this->arguments->get(),
                Method::getSignature($this->method->get(), $this->qlass->get()),
                Method::getDocumentation($this->method->get(), $this->qlass->get())
            );
        }


        // [9] Dump the result to the console
        // ==================================
        Console::message(
            $result,
            $this->arguments->get(),
            Method::getSignature($this->method->get(), $this->qlass->get()),
            Method::getDocumentation($this->method->get(), $this->qlass->get()),
            $this->debug->get()
        );
    }

    /**
     * @throws BadMethodCallException
     */
    private function page()
    {
        $page = $this->options->has('page') ? $this->options->get('page') : 0;

        if ((int) $page < 0 || !is_numeric($page) || is_float($page)) {
            $msg = 'Page Id "' . $page . '" is not a positive, whole number!';
            throw new BadMethodCallException($msg, 1364342929);
        }

        return $page;
    }

    /**
     *
     */
    private function hasCache()
    {
        return (boolean) !$this->options->get('no_cache');
    }
}

$cliObj = Compatibility::makeInstance('RTP\\CliRunner\\Cli\\Runner');
$cliObj->main($_SERVER['argv']);

