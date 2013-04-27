<?php
namespace RTP\CliRunner\Cli;

use BadMethodCallException;
use Exception;
use ReflectionMethod;
use RTP\CliRunner\Utility\File as File;
use RTP\CliRunner\Service\Frontend as Frontend;
use RTP\CliRunner\Service\Compatibility as Compatibility;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

$extensionPath = \t3lib_extMgm::extPath('cli_runner');
$extensionClassesPath = $extensionPath . 'Classes/';

require_once $extensionClassesPath . 'Cli/Options.php';
require_once $extensionClassesPath . 'Command/Arguments.php';
require_once $extensionClassesPath . 'Command/Export.php';
require_once $extensionClassesPath . 'Command/Method.php';
require_once $extensionClassesPath . 'Command/Qlass.php';
require_once $extensionClassesPath . 'Service/Compatibility.php';
require_once $extensionClassesPath . 'Service/Frontend.php';
require_once $extensionClassesPath . 'Utility/Console.php';
require_once $extensionClassesPath . 'Utility/File.php';
require_once $extensionClassesPath . 'Utility/Typo3.php';

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
     * @var \RTP\CliRunner\Command\Export
     */
    private $export;

    /**
     * @var \RTP\CliRunner\Cli\Options
     */
    private $options;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var \RTP\CliRunner\Utility\Console
     */
    private $console;

    /**
     * @param $options
     * @throws BadMethodCallException
     */
    public function main($options)
    {
        $this->options = Compatibility::makeInstance('RTP\\CliRunner\\Cli\\Options', $options);
        $this->console = Compatibility::makeInstance('RTP\\CliRunner\\Utility\\Console', $this->options);

        // Prints the help message if requested
        if ($this->options->has('help')) {
            $this->console->help();
            exit;
        }

        /**
         * [1] Simulate a frontend environment
         * ===================================
         * Create an instance of tslib_fe
         */
        Frontend::simulate($this->page(), $this->hasCache());


        /**
         * [2] Include a PHP file.
         * =======================
         * This can be any PHP file and could be used to include other files and/or
         * declare global variables.
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
            $this->console->message($msg);
        }
echo '116' . PHP_EOL;

        // [3] Set the arguments to pass to the method
        // ===========================================
        try {
            $this->arguments = Compatibility::makeInstance('RTP\\CliRunner\\Command\\Arguments', $this->options);
            $this->arguments->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }

echo '129';
        /**
         * [4] Set the class to instantiate
         * ================================
         * @see http://wiki.typo3.org/TSref/getText#TSFE:.3Ckey.3E.5B.7C.3Csubkey.3E.5B.7C.3Csubsubkey.3E....5D.5D
         * Resolves the class argument which can be a string (class name), a key pointing to a global property
         * (e.g. TSFE|cObj) or a PHP file which, when included exposes a variable $__class which points to the class
         * or class instance
         */
        try {
            $this->qlass = Compatibility::makeInstance('RTP\\CliRunner\\Command\\Qlass', $this->options);
            $this->qlass->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }


        // [5] Set the method or function to invoke
        // ========================================
        try {
            $this->method = Compatibility::makeInstance('RTP\\CliRunner\\Command\\Method', $this->options, $this->qlass);
            $this->method->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }

echo '159' . PHP_EOL;
        // [6] Execute the method
        // ======================
        try {
            $this->execute();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg, $this->method->signature());
        }


        // [7] Process the result
        // ========================================
        try {
            $this->export = Compatibility::makeInstance(
                'RTP\\CliRunner\\Command\\Export',
                $this->options,
                $this->qlass,
                $this->result
            );
            $this->export->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg, $this->method->signature());
        }
echo 'signature: ' . $this->method->signature() . PHP_EOL;
        //
        $this->console->message($this->export->get(), $this->method->signature());
    }

    /**
     * @return mixed
     */
    private function execute()
    {
        if ($this->qlass->has()) {

            $method = new ReflectionMethod($this->qlass->get(), $this->method->get());
            $method->setAccessible(true);

            if ($this->method->isStatic()) {
                $this->result = $method->invokeArgs(null, $this->arguments->get());

            } else {
                $this->result = $method->invokeArgs($this->qlass->instance(), $this->arguments->get());
            }

        } else {
            $this->result = call_user_func_array($this->method->get(), $this->arguments->get());
        }
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

$cliObj = \RTP\CliRunner\Service\Compatibility::makeInstance('RTP\\CliRunner\\Cli\\Runner');
$cliObj->main($_SERVER['argv']);

