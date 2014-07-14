<?php
namespace RTP\CliRunner\Utility;

use BadMethodCallException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class Method
 * @package RTP\CliRunner\Utility
 */
class Method
{
    /**
     * @var string Scope resolution operator
     */
    const DOUBLE_COLON_OPERATOR = '::';

    /**
     * @var string Object operator
     */
    const ARROW_OPERATOR = '->';

    /**
     * # Method is Valid
     * Performs the following checks to verify the validity of the given method:
     * - A method name is has been defined
     * - If a class has been defined the method must belong to that class
     * - If no class has been defined then the method must exist as a function
     *
     * @throws BadMethodCallException
     */
    public static function isValid($methodName, $class = null)
    {
        if (!$methodName) {

            // [1.] In order to be valid a method must be defined!
            $msg = 'Missing required method name!';
            throw new BadMethodCallException($msg, 1354959022);

        } elseif (!is_null($class) && !self::methodExists($methodName, $class)) {

            // [2.] Given a class the method must be a member of that class
            $msg = 'Method "' . $methodName . '" not available in class "' . $class . '"!';
            throw new BadMethodCallException($msg, 1354959172);

        } elseif (is_null($class) && !function_exists($methodName)) {

            // [3.] If no class was defined the method must exist as a function
            $msg = 'Unknown function "' . $methodName . '" or missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);
        }

        // True if no exception was thrown.
        return true;
    }

    /**
     * # Method Exists
     *
     * @param string $methodName
     * @param string|object $className
     * @return bool
     */
    public static function methodExists($methodName, $className)
    {
        $class = new ReflectionClass($className);
        return $class->hasMethod($methodName);
    }

    /**
     * # Method Operator
     * Returns the scope resolution or arrow operator depending on whether the method is static.
     *
     * @param string $methodName
     * @param null|string|object $class
     * @return string
     */
    public function getOperator($methodName, $class = null)
    {
        return self::isStatic($methodName, $class) ? self::DOUBLE_COLON_OPERATOR : self::ARROW_OPERATOR;
    }

    /**
     * # Method Signature
     * Returns a readable version of the method call, for example ```t3lib_db->exec_SELECTgetRows```
     *
     * @param $methodName
     * @param null|string|object $class
     * @return string
     */
    public static function getSignature($methodName, $class = null)
    {
        return (!is_null($class) ? Qlass::getName($class) . self::getOperator($methodName, $class) : '') . $methodName;
    }

    /**
     * # Method Documentation
     * Returns a methods doc comments
     *
     * @param string $methodName
     * @param null|string|object $class
     * @return string
     */
    public static function getDocumentation($methodName, $class = null)
    {
        if (!is_null($class)) {
            $method = new ReflectionMethod($class, $methodName);

        } else {
            $method = new ReflectionFunction($methodName);
        }

        return $method->getDocComment();
    }

    /**
     * # Method Binding
     * Returns true if the method is static.
     *
     * @param string $methodName
     * @param null|string|object $class
     * @return bool
     */
    public static function isStatic($methodName, $class = null)
    {
        $isStatic = false;

        if (!is_null($class)) {
            $method = new ReflectionMethod($class, $methodName);

            if ($method->isStatic()) {
                $isStatic = true;
            }
        }

        return $isStatic;
    }

    /**
     * # Execute Method
     * Invokes a any method (i.e. public, protected or private) or function with the given arguments.
     *
     * @param string $methodName
     * @param array $arguments
     * @param null|string|object $class
     * @return mixed
     */
    public static function execute($methodName, array $arguments = array(), $class = null)
    {
        if (!is_null($class)) {

            $method = new ReflectionMethod($class, $methodName);
            $method->setAccessible(true);

            if (self::isStatic($methodName, $class)) {
                $result = $method->invokeArgs(null, $arguments);

            } else {
                $result = $method->invokeArgs(Qlass::getInstance($class), $arguments);
            }

        } else {
            $result = call_user_func_array($methodName, $arguments);
        }

        return $result;
    }
}

