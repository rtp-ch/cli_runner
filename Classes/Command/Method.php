<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;
use ReflectionClass;

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
     * @var Scope resolution operator
     */
    const DOUBLE_COLON_OPERATOR = '::';

    /**
     * @var Object operator
     */
    const ARROW_OPERATOR = '->';

    /**
     * @param $options
     * @param $qlass
     */
    public function __construct($options, $qlass)
    {
        $this->options = $options;
        $this->qlass = $qlass;
    }

    /**
     * @throws BadMethodCallException
     */
    public function set()
    {
        // If the method has already been defined set it from the $__method variable
        if (isset($__method)) {
            $this->method = (string) $__method;

        } elseif (File::isValid($this->options->get('method'))) {
            // If the argument is a file then attempt to load the file. The method name
            // should be included in a variable $__method in the file which is being loaded.
            File::load($this->options->get('method'));

            // Must be exposed in a variable called $__method.
            if (isset($__method)) {
                $this->method = (string) $__method;

            } else {
                $msg = 'Missing $__method variable in "' . $this->options->get('method') . '"!';
                throw new BadMethodCallException($msg, 1366567236);
            }

        } else {
            // Finally the argument is assumed to the method name
            $this->method = $this->options->get('method');
        }

        // Method is required
        if (!$this->has()) {
            $msg = 'Missing required method name!';
            throw new BadMethodCallException($msg, 1354959022);
        }

        // Checks that the given class has a corresponding method
        if ($this->qlass->has()) {

            $qlass = new ReflectionClass($this->qlass->get());
            if (!$qlass->hasMethod($this->get())) {
                $msg = 'Method "' . $this->get() . '" not available in class "' . $this->get() . '"!';
                throw new BadMethodCallException($msg, 1354959172);
            }

        } elseif (!$this->qlass->has() && !function_exists($this->get())) {
            // Or, if no class was defined, that the function exists
            $msg = 'Unknown function "' . $this->get() . '" or missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);
        }
    }

    /**
     * Checks if the class has been defined
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * Returns the class name or instance
     *
     * @return mixed
     */
    public function get()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function operator()
    {
        return $this->isStatic() ? self::DOUBLE_COLON_OPERATOR : self::ARROW_OPERATOR;
    }

    /**
     * @return string
     */
    public function signature()
    {
        return ($this->qlass->has() ? $this->qlass->name() . $this->operator() : '') . $this->get();
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        static $isStatic;

        if (is_null($isStatic)) {

            $isStatic = false;

            if ($this->qlass->has()) {
                $method = new \ReflectionMethod($this->qlass->name(), $this->get());

                if ($method->isStatic()) {
                    $isStatic = true;
                }
            }
        }

        return $isStatic;
    }
}

