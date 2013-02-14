<?php

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

class rtp_cli
    extends t3lib_cli
{
    /**
     * @var
     */
    const STATIC_TYPE_LONG          = 'static';

    /**
     * @var
     */
    const INITIALIZE_TYPE_LONG      = 'initialize';

    /**
     * @var
     */
    const STATIC_TYPE_SHORT         = 's';

    /**
     * @var
     */
    const INITIALIZE_TYPE_SHORT     = 'i';

    /**
     * @var
     */
    private $instance;

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
    private $type;

    /**
     * @var
     */
    private $args;

    /**
     * @var array
     */
    static private $options = array(
        'c' => 'class',
        'm' => 'method',
        't' => 'type',
        'a' => 'args',
        'f' => 'file'
    );

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

        //
        try {
            $this->includeFile();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        $this->setType();

        //
        try {
            $this->setClassAndInstance();

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
            if ($this->hasClass()) {
                $callback = array($this->getClassOrInstance(), $this->getMethod());
                $result = call_user_func_array($callback, $this->getArgs());

            } else {
                $result = call_user_func_array($this->getMethod(), $this->getArgs());
            }


        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
        }

        //
        $this->printMsg($result);
    }

    /**
     * @param $result
     */
    private function printMsg($result)
    {
        $methodCall  = $this->hasClass() ? $this->getClass() . $this->getMethodCall() : '';
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
     * Prints help message
     */
    private function printHelp()
    {
        $help  = '...' . PHP_EOL;

        echo $help;
    }

    /**
     * @throws BadMethodCallException
     */
    private function setClassAndInstance()
    {
        $this->class = $this->arguments['class'];

        if ($this->hasClass()) {
            $this->instance = t3lib_div::makeInstance($this->class);
        }
    }

    /**
     * @return mixed
     */
    private function getClassOrInstance()
    {
        if ($this->hasClass()) {
            if ($this->type === self::STATIC_TYPE_SHORT || $this->type === self::STATIC_TYPE_LONG) {
                return $this->class;

            } else {
                return $this->instance;
            }
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
     * @return bool
     */
    private function hasClass()
    {
        return (boolean) $this->class;
    }

    /**
     * @return string
     */
    private function getMethodCall()
    {
        return $this->isStatic() ? '::' : '->';
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

        if ($this->hasClass() && !method_exists($this->instance, $this->getMethod())) {
            $msg = 'Method "' . $this->method . '" not available in class "' . $this->class . '"!';
            throw new BadMethodCallException($msg, 1354959172);

        } elseif (!$this->hasClass() && !function_exists($this->getMethod())) {
            $msg = 'Unknown function "' . $this->getMethod() . '" or missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);
        }
    }


    /**
     * @return mixed
     */
    private function getMethod()
    {
        return $this->method;
    }

    /**
     *
     */
    private function setType()
    {
        $this->type = trim(strtolower($this->arguments['type']));
        if ($this->type === self::STATIC_TYPE_SHORT || $this->type === self::STATIC_TYPE_LONG) {
            $this->type = self::STATIC_TYPE_LONG;

        } else {
            $this->type = self::INITIALIZE_TYPE_LONG;
        }
    }

    /**
     * @return mixed
     */
    private function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    private function isStatic()
    {
        return ($this->getType() === self::STATIC_TYPE_SHORT || $this->getType() === self::STATIC_TYPE_LONG);
    }

    /**
     *
     */
    private function setArgs()
    {
        if ($this->arguments['args']) {
            $file = $this->arguments['args'];

            if ($file) {
                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName($this->arguments['args']);
                }

                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $this->arguments['args']);
                }

                if (is_file($file) && is_readable($file)) {
                    $this->args = json_decode(file_get_contents($file), true);

                } else {
                    $msg = 'Unable to read file "' . $this->arguments['args'] . '"';
                    throw new BadMethodCallException($msg, 1354965903);
                }
            }
        }
    }

    /**
     * @return mixed
     */
    private function getArgs()
    {
        return is_array($this->args) && !empty($this->args) ? $this->args : array();
    }

    /**
     * @throws BadMethodCallException
     */
    private function includeFile()
    {
        if ($this->arguments['file']) {
            $file = $this->arguments['file'];

            if ($file) {
                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName($this->arguments['file']);
                }

                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $this->arguments['file']);
                }

                if (is_file($file) && is_readable($file)) {
                    require_once $file;

                } else {
                    $msg = 'Unable to include file "' . $this->arguments['file'] . '"';
                    throw new BadMethodCallException($msg, 1360849885);
                }
            }
        }
    }
}

$cliObj = t3lib_div::makeInstance('rtp_cli');
$cliObj->cli_main($_SERVER['argv']);

