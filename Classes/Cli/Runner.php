<?php
namespace RTP\RtpCli\Cli;

use BadMethodCallException;
use Exception;
use ReflectionMethod;
use RTP\RtpCli\Utility\File as File;
use RTP\RtpCli\Service\Frontend as Frontend;
use RTP\RtpCli\Service\Compatibility as Compatibility;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

/**
 * Class Cli
 * @package RTP\RtpCli
 */
class Runner extends \t3lib_cli
{

    /**
     * @var \RTP\RtpCli\Command\Method
     */
    private $method;

    /**
     * @var \RTP\RtpCli\Command\Qlass
     */
    private $qlass;

    /**
     * @var \RTP\RtpCli\Command\Arguments
     */
    private $arguments;

    /**
     * @var \RTP\RtpCli\Command\Export
     */
    private $export;

    /**
     * @var \RTP\RtpCli\Cli\Options
     */
    private $options;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var \RTP\RtpCli\Utility\Console
     */
    private $console;

    /**
     * @param $options
     * @throws BadMethodCallException
     */
    public function cli_main($options)
    {
        $this->options = Compatibility::makeInstance('\RTP\RtpCli\Options', $options);
        $this->console = Compatibility::makeInstance('\RTP\RtpCli\Console', $options);

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
         * declare variables etc.
         */
        try {
            if ($this->options->has('file')) {
                if (File::isValid($this->options->get('file'))) {
                    File::load($this->options->get('file'));

                } else {
                    $msg  = 'Invalid file type "' . $this->options->get('file') . '"!';
                    $msg .= 'File must have one of the following extensions: ' . implode(',', File::getValidTypes());
                    throw new BadMethodCallException($msg, 1366569266);
                }
            }

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }


        // [3] Set the arguments to pass to the method
        // ===========================================
        try {
            $this->arguments = Compatibility::makeInstance('\RTP\RtpCli\Arguments', $this->options);
            $this->arguments->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }


        /**
         * [4] Set the class to instantiate
         * ================================
         * @see http://wiki.typo3.org/TSref/getText#TSFE:.3Ckey.3E.5B.7C.3Csubkey.3E.5B.7C.3Csubsubkey.3E....5D.5D
         * Resolves the class argument which can be a string (class name), a key pointing to a global property
         * (e.g. TSFE|cObj) or a PHP file which, when included exposes a variable $__class which points to the class
         * or class instance
         */
        try {
            $this->qlass = Compatibility::makeInstance('\RTP\RtpCli\Qlass', $this->options);
            $this->qlass->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }


        // [5] Set the method or function to invoke
        // ========================================
        try {
            $this->method = Compatibility::makeInstance('\RTP\RtpCli\Method', $this->options, $this->qlass);
            $this->method->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg);
        }


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
                '\RTP\RtpCli\Export',
                $this->options,
                $this->qlass,
                $this->result
            );
            $this->export->set();

        } catch (Exception $e) {
            $msg = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->console->message($msg, $this->method->signature());
        }

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

$cliObj = Compatibility::makeInstance('RTP\RtpCli\Cli');
$cliObj->cli_main($_SERVER['argv']);

