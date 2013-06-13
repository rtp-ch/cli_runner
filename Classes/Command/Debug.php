<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;
use RTP\CliRunner\Utility\Typo3 as Typo3;
use RTP\CliRunner\Service\Compatibility as Compatibility;

class Debug
{
    /**
     * @var mixed
     */
    private $debug;

    /**
     * @var \RTP\CliRunner\Command\Qlass
     */
    private $qlass;

    /**
     * @var \RTP\CliRunner\Cli\Options
     */
    private $options;

    /**
     * # Constructor
     * Gets an instance of the command line options and the class handler and the result of the method execution.
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
     * # Gather Debug Information
     * Retrieves the result of the variable/property/class which the the command line option
     * ```--debug``` points to. The option can define any of the following:
     * - The name of a property of the current class, e.g. ```myExtVar``` would look for the result in
     *   ```ty_myext_pi1->myExtVar```.
     * - The path to a PHP file which, when included defines a global variable ```$_cli_debug``` which points
     *   to the result of the operation,
     * - A global variable ```$GLOBALS['TYPO3_DB']```
     * - A value from the global scope, such as ```$GLOBALS['TSFE']->id```
     * - A property of the class which the method that was executed was a member of
     *
     * @param null $result
     * @throws \BadMethodCallException
     */
    public function set($result = null)
    {
        // Option #1 is if the global variable $_cli_debug has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_debug'])) {
            if (is_callable($GLOBALS['_cli_debug'])) {
                $this->debug = $GLOBALS['_cli_debug']($result);

            } else {
                $this->debug =& $GLOBALS['_cli_debug'];
            }

        } elseif ($this->options->has('debug')) {

            // Option #2 is is to set the global variable _cli_debug in an included PHP file
            if (File::isValid($this->options->get('debug'))) {

                // Include the file and check for the global variable ```$_cli_debug```
                File::load($this->options->get('debug'));
                if (isset($GLOBALS['_cli_debug'])) {
                    if (is_callable($GLOBALS['_cli_debug'])) {
                        $this->debug = $GLOBALS['_cli_debug']($result);

                    } else {
                        $this->debug =& $GLOBALS['_cli_debug'];
                    }

                } else {
                    $msg = 'Missing $_cli_debug variable in "' . $this->options->get('debug') . '"!';
                    throw new BadMethodCallException($msg, 1364487850);
                }

            // Option #3 is to retrieve the result from a static variable (e.g. ```tx_myext_pi1::someConst```)
            // defined in the debug command line option
            } elseif (strpos($this->options->get('debug'), '::')) {

                // Resolves the definition of the static variable
                $exports = Compatibility::trimExplode('::', $this->options->get('debug'), true, 2);
                $class = $exports[0];
                $name  = $exports[1];

                // Access the static variable, even if it's private
                $property = new ReflectionProperty($class, $name);
                $property->setAccessible(true);
                $this->debug = $property->getValue();

            // Option #4 is to debug an existing global, for example ```--debug TYPO3_DB```.
            } elseif (is_object($GLOBALS[$this->options->get('debug')])) {
                $this->debug = $GLOBALS[$this->options->get('debug')];

            // Option #5 is to debug an item from the global scope similar to the TYPO3 getText functionality.
            // For example ```TSFE|id``` will retrieve the current page id.
            } elseif (strpos($this->options->get('debug'), '|')) {
                $this->debug = Typo3::getGlobal($this->options->get('debug'));

            // Option #6 is to assume the command line option ```--debug``` defines a property of the current class.
            } else {
                $property = new ReflectionProperty($this->qlass->get(), $this->options->get('debug'));
                $property->setAccessible(true);
                $this->debug = $property->getValue($this->qlass->instance());
            }
        }
    }

    /**
     * # Debug Data
     *
     * @return mixed
     */
    public function get()
    {
        // Executes the debug statement if it's a closure
        if (is_callable($this->debug)) {
            return $GLOBALS['_cli_debug']();

        } else {
            // Otherwise content of debug variable is returned as is
            return $this->debug;
        }
    }

    /**
     * # Is Defined
     * Checks if the debug option has been defined
     *
     * @return bool
     */
    public function has()
    {
        return $this->options->has('debug');
    }
}

