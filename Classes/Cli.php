<?php
namespace RTP\RtpCli;

use BadMethodCallException;
use Exception;
use RTP\RtpCli\File as File;
use RTP\RtpCli\Frontend as Frontend;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use t3lib_cli;
use t3lib_div;
use t3lib_extMgm;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

require_once t3lib_extMgm::extPath('rtp_cli') . 'Classes/Frontend.php';
require_once t3lib_extMgm::extPath('rtp_cli') . 'Classes/File.php';

class Cli extends t3lib_cli
{
    /**
     * @var array
     */
    static private $options = array(
        'c' => 'class',
        'm' => 'method',
        'a' => 'args',
        'f' => 'file',
        'p' => 'page',
        'v' => 'env',
        'n' => 'no_cache',
        'e' => 'export',
    );
    /**
     * @var
     */
    private $method;
    /**
     * @var
     */
    private $class;
    /**
     * @var
     */
    private $static;
    /**
     * @var
     */
    private $page;
    /**
     * @var
     */
    private $args;
    /**
     * @var
     */
    private $noCache;
    /**
     * @var
     */
    private $export;
    /**
     * @var array
     */
    private $arguments = array();

    /**
     * @param $argv
     * @throws BadMethodCallException
     */
    public function cli_main($argv)
    {
        $opts   = $argv;
        $script = array_shift($opts);

        // Prints the help message if requested
        if (in_array('-?', $opts) || in_array('--help', $opts)) {
            $this->printHelp();
            exit;
        }

        // Parses the cli arguments
        $opts = array_chunk($opts, 2);
        foreach ($opts as $opt) {

            $option = strtolower(str_replace('-', '', $opt[0]));

            if (in_array($option, self::$options)) {
                $this->arguments[$option] = $opt[1];

            } elseif (isset(self::$options[$option])) {
                $this->arguments[self::$options[$option]] = $opt[1];
            }
        }

        /**
         * [1] Simulate a frontend environment, i.e. create an instance of tslib_fe
         */
        // TODO: Alternate Environments, e.g. backend
        if (!isset($this->arguments['env'])
            || strtolower($this->arguments['env']) === 'fe'
            || strtolower($this->arguments['env']) === 'frontend') {

            Frontend::simulate($this->getPage(), $this->getNoCache());
        }

        /**
         * [2] Includes a PHP file.
         */
        try {
            $this->includeFile();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        /**
         * [3] Set the class to instantiate
         */
        try {
            $this->setClass();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setMethod();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setArgs();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setExport();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setPage();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        $this->setNoCache();

        //
        try {
            if ($this->hasClass()) {

                $method = new ReflectionMethod($this->getClass(), $this->getMethod());
                $method->setAccessible(true);

                if ($this->isStatic()) {
                    $result = $method->invokeArgs(null, $this->getArgs());

                } else {
                    $result = $method->invokeArgs($this->getClassInstance(), $this->getArgs());
                }

            } else {
                $result = call_user_func_array($this->getMethod(), $this->getArgs());
            }


        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        if (isset($this->arguments['export'])) {
            $this->printMsg($this->getExport());

        } else {
            $this->printMsg($result);
        }

    }

    /**
     * Prints help message
     */
    private function printHelp()
    {
        $help  = '...' . PHP_EOL;

        echo $help;
    }

    /**
     * @return int
     */
    private function getPage()
    {
        return $this->page;
    }

    /**
     * @return bool
     */
    private function getNoCache()
    {
        return $this->noCache;
    }

    /**
     *
     */
    private function includeFile()
    {
        File::load($this->arguments['file']);
    }

    /**
     * @param $result
     */
    private function printMsg($result)
    {
        $methodCall  = $this->hasClass() ? $this->getClassName() . $this->getMethodCall() : '';
        $methodCall .= $this->getMethod();
        $message     = 'Output of "' . $methodCall . '" with arguments:';
        $border      = str_repeat('=', strlen($message));

        echo "\n" . $border . "\n";
        echo $message . "\n";
        echo $border  . "\n\n";

        print_r($this->getArgs());

        echo "\n" . $border . "\n\n";

        print_r($result);

        echo "\n\n" . $border . "\n";
        exit;
    }

    /**
     * @return bool
     */
    private function hasClass()
    {
        return (boolean) $this->class;
    }

    private function getClassName()
    {
        if (is_object($this->getClass())) {
            return get_class($this->getClass());

        } else {
            return $this->getClass();
        }
    }

    /**
     * @return mixed
     */
    private function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    private function getMethodCall()
    {
        return $this->isStatic() ? '::' : '->';
    }

    /**
     * @return bool
     */
    private function isStatic()
    {
        if (is_null($this->static)) {
            $this->static = false;

            if ($this->hasClass()) {
                $method = new ReflectionMethod($this->getClassName(), $this->getMethod());

                if ($method->isStatic()) {
                    $this->static = true;
                }
            }
        }

        return $this->static;
    }

    /**
     * @return mixed
     */
    private function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    private function getArgs()
    {
        return is_array($this->args) && !empty($this->args) ? $this->args : array();
    }

    /**
     *
     */
    private function setClass()
    {
        if (File::isPhp($this->arguments['class'])) {
            File::load($this->arguments['class']);

            // Must be exposed in a variable called $class.
            if (isset($class)) {
                $this->class = $class;

            } else {
                $msg = 'Missing $class variable in "' . $this->arguments['class'] . '"!';
                throw new BadMethodCallException($msg, 1364487850);
            }

            $this->class =& $class;

        } elseif (is_object($GLOBALS[$this->arguments['class']])) {
            $this->class = $GLOBALS[$this->arguments['class']];

        } elseif (strpos($this->arguments['class'], '|')) {
            $this->class = $GLOBALS['TSFE']->cObj->getGlobal($this->arguments['class']);

        } else {
            $class = $GLOBALS['TSFE']->cObj->getGlobal($this->arguments['class']);
            if ($class) {
                $this->class = $this->arguments['class'];

            } else {
                $this->class = $this->arguments['class'];
            }
        }
    }

    /**
     * @throws BadMethodCallException
     */
    private function setMethod()
    {
        $this->method = $this->arguments['method'];

        if (!$this->method) {
            $msg = 'Missing required method name!';
            throw new BadMethodCallException($msg, 1354959022);
        }

        if ($this->hasClass()) {

            $reflectionClass = new ReflectionClass($this->getClass());

            if (!$reflectionClass->hasMethod($this->getMethod())) {
                $msg = 'Method "' . $this->method . '" not available in class "' . $this->class . '"!';
                throw new BadMethodCallException($msg, 1354959172);
            }

        } elseif (!$this->hasClass() && !function_exists($this->getMethod())) {
            $msg = 'Unknown function "' . $this->getMethod() . '" or missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);
        }
    }

    /**
     * @throws BadMethodCallException
     */
    private function setArgs()
    {
        if ($this->arguments['args']) {

            if (File::isPhp($this->arguments['args'])) {
                File::load($this->arguments['args']);

                // If arguments are included in a php file the arguments must be exposed in
                // an array called $_args.
                if (isset($_args)) {
                    $this->args =& (array) $_args;

                } else {
                    $msg = 'Missing $_args variable in "' . $this->arguments['args'] . '"!';
                    throw new BadMethodCallException($msg, 1364419871);
                }

            } else {
                $msg = 'Unknown file type "' . $this->arguments['args'] . '" in --args option!';
                throw new BadMethodCallException($msg, 1364419931);
            }
        }
    }

    private function setExport()
    {
        $this->export = isset($this->arguments['export']) ? $this->arguments['export'] : null;
    }

    /**
     * @throws BadMethodCallException
     */
    private function setPage()
    {
        $page = isset($this->arguments['page']) ? $this->arguments['page'] : 0;

        if ((int) $page < 0 || !is_numeric($page) || is_float($page)) {
            $msg = 'Page Id "' . $this->arguments['page'] . '" is not a positive, whole number!';
            throw new BadMethodCallException($msg, 1364342929);
        }

        $this->page = (int) $page;
    }

    /**
     *
     */
    private function setNoCache()
    {
        $this->noCache = (boolean) $this->arguments['no_cache'];
    }

    private function getClassInstance()
    {
        if (
            is_object($this->getClass())) {
            return $this->getClass();

        } else {
            $this->class = t3lib_div::makeInstance($this->getClass());

        }
    }

    private function hasExport()
    {
        return isset($this->arguments['export']);
    }

    /**
     * @return mixed
     * @throws \BadMethodCallException
     */
    private function getExport()
    {
        if ($this->hasExport()) {
            if (File::isPhp($this->arguments['export'])) {
                File::load($this->arguments['export']);

                // Must be exposed in a variable called $_export
                if (isset($_export)) {
                    $this->export = ${$_export};

                } else {
                    $msg = 'Missing $_export variable in "' . $this->arguments['export'] . '"!';
                    throw new BadMethodCallException($msg, 1364487850);
                }

            } elseif (strpos($this->arguments['export'], '::')) {

                $exports = t3lib_div::trimExplode('::', $this->arguments['export'], true, 2);
                $class = $exports[0];
                $name  = $exports[1];

                $property = new ReflectionProperty($class, $name);
                $property->setAccessible(true);
                $this->export = $property->getValue();

            } elseif (is_object($GLOBALS[$this->arguments['export']])) {
                $this->export = $GLOBALS[$this->arguments['export']];

            } elseif (strpos($this->arguments['export'], '|')) {
                $this->export = $GLOBALS['TSFE']->cObj->getGlobal($this->arguments['export']);

            } else {
                $property = new ReflectionProperty($this->getClass(), $this->arguments['export']);
                $property->setAccessible(true);
                $this->export = $property->getValue($this->getClassInstance());
            }

            if (is_object($this->export)) {
                return json_decode(json_encode($this->export), true);

            } else {
                return $this->export;
            }
        }
    }
}

$cliObj = t3lib_div::makeInstance('RTP\RtpCli\Cli');
$cliObj->cli_main($_SERVER['argv']);

