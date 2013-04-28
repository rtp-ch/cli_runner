<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class Method
 * @package RTP\CliRunner\Command
 */
class Method
{
    /**
     * @var string Name of the method or function
     */
    private $method;

    /**
     * @var \RTP\CliRunner\Command\Qlass
     */
    private $qlass;

    /**
     * @var \RTP\CliRunner\Cli\Options
     */
    private $options;

    /**
     * @var string Scope resolution operator
     */
    const DOUBLE_COLON_OPERATOR = '::';

    /**
     * @var string Object operator
     */
    const ARROW_OPERATOR = '->';

    /**
     * # Constructor
     * Gets an instance of the command line options and the class handler
     *
     * @param $options
     * @param $qlass
     */
    public function __construct($options, $qlass)
    {
        $this->options = $options;
        $this->qlass = $qlass;
    }

    /**
     * # Define the Method Name
     * Sets the name of the function or method from the command line options. The command line option ```--method```
     * can either define the method name or point to a PHP file. If it points to a PHP file the file must return
     * the method name or define a global variable ```_cli_method```. For example:
     *
     *      $GLOBALS['_cli_method'] = 'exec_SELECTgetRows';
     *
     * @throws BadMethodCallException
     */
    public function set()
    {
        // Option #1 is if the global variable $_cli_method has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_method'])) {
            $this->method = (string) $GLOBALS['_cli_method'];

        } else if ($this->options->has('method')) {

            // Option #2 is to include a PHP file which defines the method
            if (File::isValid($this->options->get('method'))) {

                // Option #2a is to return string (the method name) from the included PHP file
                $method = File::load($this->options->get('method'));
                if (is_string($method)) {
                    $this->method = $method;

                // Option #2b is to set the global variable _cli_method in the included PHP file
                } else if (isset($GLOBALS['_cli_method'])) {
                    $this->method = (string) $GLOBALS['_cli_method'];

                } else {
                    $msg = 'Missing $__method variable in "' . $this->options->get('method') . '"!';
                    throw new BadMethodCallException($msg, 1366567236);
                }

            // Option #3 is when the command line option is the method name.
            } else {
                $this->method = $this->options->get('method');
            }
        }
    }

    /**
     * # Validate the Method
     * Performs the following checks to verify the validity of the method:
     * - A method name is required
     * - If a class has been defined the method must belong to that class
     * - If no class has been defined then the method must exist as a function
     *
     * @throws BadMethodCallException
     */
    public function isValid()
    {
        // [1.] Method is required!
        if (!$this->has()) {
            $msg = 'Missing required method name!';
            throw new BadMethodCallException($msg, 1354959022);
        }

        // [2.] Checks that the given class has a corresponding method
        if ($this->qlass->has()) {

            $qlass = new ReflectionClass($this->qlass->get());
            if (!$qlass->hasMethod($this->get())) {
                $msg = 'Method "' . $this->get() . '" not available in class "' . $this->get() . '"!';
                throw new BadMethodCallException($msg, 1354959172);
            }

        // [3.] If no class was defined the method must exist as a function
        } elseif (!$this->qlass->has() && !function_exists($this->get())) {
            $msg = 'Unknown function "' . $this->get() . '" or missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);
        }
    }

    /**
     * # Is Defined
     * Checks if a method name has been defined
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * # Return the Method Name
     * Returns the name of the method
     *
     * @return string
     */
    public function get()
    {
        return $this->method;
    }

    /**
     * # Method Operator
     * Returns the scope resolution or arrow operator depending on whether the method is static.
     *
     * @return string
     */
    public function operator()
    {
        return $this->isStatic() ? self::DOUBLE_COLON_OPERATOR : self::ARROW_OPERATOR;
    }

    /**
     * # Method Signature
     * Returns a readable version of the method call, for example ```t3lib_db->exec_SELECTgetRows```
     *
     * @return string
     */
    public function signature()
    {
        return ($this->qlass->has() ? $this->qlass->name() . $this->operator() : '') . $this->get();
    }

    /**
     * # Method Binding
     * Returns true if the method is static.
     *
     * @return bool
     */
    public function isStatic()
    {
        static $isStatic;

        if (is_null($isStatic)) {

            $isStatic = false;

            if ($this->qlass->has()) {
                $method = new ReflectionMethod($this->qlass->name(), $this->get());

                if ($method->isStatic()) {
                    $isStatic = true;
                }
            }
        }

        return $isStatic;
    }
}

