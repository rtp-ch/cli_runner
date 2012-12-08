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
        'a' => 'args'
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
        $this->setType();
        $this->setArgs();

        //
        try {
            $result = call_user_func_array(array($this->getClassOrInstance(), $this->getMethod()), $this->getArgs());

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
        }

        $this->printMsg($result);
    }

    private function printMsg($result)
    {
        $methodCall = $this->getClass() . $this->getMethodCall() . $this->getMethod();
        $message    = 'Output of "' . $methodCall . '" with arguments:';
        $border     = str_repeat('=', strlen($message));

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
        if (!$this->class) {
            $msg = 'Missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);

        } else {
            $this->instance = t3lib_div::makeInstance($this->class);
        }
    }

    /**
     * @return mixed
     */
    private function getClassOrInstance()
    {
        if ($this->type === self::STATIC_TYPE_SHORT || $this->type === self::STATIC_TYPE_LONG) {
            return $this->instance;

        } else {
            return $this->class;
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
     * @return mixed
     */
    private function getInstance()
    {
        return $this->instance;
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

        } elseif (!method_exists($this->instance, $this->method)) {
            $msg = 'Method "' . $this->method . '" not available in class "' . $this->class . '"!';
            throw new BadMethodCallException($msg, 1354959172);
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
        $this->args = json_decode($this->arguments['type'], true);
    }

    /**
     * @return mixed
     */
    private function getArgs()
    {
        return $this->args;
    }
}

$cliObj = t3lib_div::makeInstance('rtp_cli');
$cliObj->cli_main($_SERVER['argv']);

