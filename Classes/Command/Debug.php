<?php
namespace RTP\CliRunner\Command;

use ReflectionProperty;
use RTP\CliRunner\Cli\Options;
use RTP\CliRunner\Utility\Typo3;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Debug
{
    /**
     * @var mixed
     */
    private $debug;

    /**
     * @var Qlass
     */
    private $qlass;

    /**
     * @var Options
     */
    private $options;

    /**
     * # Constructor
     * Gets an instance of the command line options and the class handler and the result of the method execution.
     *
     * @param Options $options
     * @param Qlass   $qlass
     */
    public function __construct(Options $options, Qlass $qlass)
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
     * - A global variable ```$GLOBALS['TYPO3_DB']```
     * - A value from the global scope, such as ```$GLOBALS['TSFE']->id```
     * - A property of the class which the method that was executed was a member of
     *
     * @param null $result
     */
    public function set($result = null)
    {

        if (isset($GLOBALS['_cli_debug'])) {

            // The global variable $_cli_debug has already been defined (e.g. from a PHP file which
            // has already been included).
            $this->evalDebug($GLOBALS['_cli_debug'], $result);

        } elseif ($this->options->has('debug')) {

            // The debug  option is set via the command line
            $this->evalDebug($this->options->get('debug'), $result);
        }
    }

    /**
     * @param $debug
     * @param $result
     */
    private function evalDebug($debug, $result)
    {
        if (is_callable($debug)) {

            $this->debug = $debug($result);

        } elseif (is_string($debug)) {

            if (strpos($debug, '::')) {

                // Resolves the definition of the static variable
                $exports = GeneralUtility::trimExplode('::', $debug, true, 2);
                $class = $exports[0];
                $name  = $exports[1];

                // Access the static variable, even if it's private
                $property = new ReflectionProperty($class, $name);
                $property->setAccessible(true);
                $this->debug = $property->getValue();

            } elseif (is_object($GLOBALS[$debug])) {

                // Option #4 is to debug an existing global, for example ```--debug TYPO3_DB```.
                $this->debug = $GLOBALS[$debug];

            } elseif (strpos($debug, '|')) {

                // Option #5 is to debug an item from the global scope similar to the TYPO3 getText functionality.
                // For example ```TSFE|id``` will retrieve the current page id.
                $this->debug = Typo3::getGlobal($debug);

            } else {

                // Option #6 is to assume the command line option ```--debug``` defines a property of the current class.
                $property = new ReflectionProperty($this->qlass->get(), $debug);
                $property->setAccessible(true);
                $this->debug = $property->getValue($this->qlass->get());

            }
        } else {
            $this->debug =& $GLOBALS['_cli_debug'];
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
