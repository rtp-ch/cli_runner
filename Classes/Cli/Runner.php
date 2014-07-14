<?php
namespace RTP\CliRunner\Cli;

use BadMethodCallException;
use Exception;
use RTP\CliRunner\Command\Arguments;
use RTP\CliRunner\Command\Debug;
use RTP\CliRunner\Command\Qlass;
use RTP\CliRunner\Utility\Method;
use RTP\CliRunner\Utility\Console;
use RTP\CliRunner\Utility\File;
use RTP\CliRunner\Service\Frontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
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
     * @var Qlass
     */
    private $qlass;

    /**
     * @var Arguments
     */
    private $arguments;

    /**
     * @var Debug
     */
    private $debug;

    /**
     * @var Setup
     */
    private $setup;

    /**
     * @var Options
     */
    private $options;

    /**
     * @param $options
     * @throws BadMethodCallException
     */
    public function main(Options $options)
    {
        $this->options = GeneralUtility::makeInstance('RTP\\CliRunner\\Cli\\Options', $options);

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
            $this->setup = GeneralUtility::makeInstance('RTP\\CliRunner\\Cli\\Setup', $this->options);
            $this->setup->set();
            $this->setup->run();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        // [4] Set the arguments to pass to the method
        // ===========================================
        try {
            $this->arguments = GeneralUtility::makeInstance('RTP\\CliRunner\\Command\\Arguments', $this->options);
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
            $this->qlass = GeneralUtility::makeInstance('RTP\\CliRunner\\Command\\Qlass', $this->options);
            $this->qlass->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            Console::message($msg, $this->options->get());
        }


        // [6] Set the method or function to invoke
        // ========================================
        try {
            $this->method = GeneralUtility::makeInstance('RTP\\CliRunner\\Command\\Method', $this->options);
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
            $this->debug = GeneralUtility::makeInstance(
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

$cliObj = GeneralUtility::makeInstance('RTP\\CliRunner\\Cli\\Runner');
$cliObj->main($_SERVER['argv']);
