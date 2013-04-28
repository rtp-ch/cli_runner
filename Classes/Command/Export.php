<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;
use RTP\CliRunner\Utility\Typo3 as Typo3;
use RTP\CliRunner\Service\Compatibility as Compatibility;

class Export
{
    /**
     * @var mixed
     */
    private $export;

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
     * @param $result
     */
    public function __construct($options, $qlass, $result)
    {
        $this->options = $options;
        $this->qlass = $qlass;
        $this->export = $result; // Defaults to the direct result of the method call.
    }

    /**
     * # Process the Result
     * Sets the name of the variable which contains the result of the method call from the command line option
     * ```--export```. The option can define any of the following:
     * - The name of a property of the current class, e.g. ```myExtVar``` would look for the result in
     *   ```ty_myext_pi1->myExtVar```.
     * - The path to a PHP file which, when included defines a global variable ```$_cli_export``` which points
     *   to the result of the operation,
     * - A global variable ```$GLOBALS['TYPO3_DB']```
     * - A value from the global scope, such as ```$GLOBALS['TSFE']->id```
     * - A property of the class which the method that was executed was a member of
     *
     * @throws \BadMethodCallException
     */
    public function set()
    {
        // Option #1 is if the global variable $_cli_export has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_export'])) {
            $this->export =& ${$GLOBALS['_cli_export']};

        } else if ($this->options->has('export')) {

            // Option #2 is is to set the global variable _cli_export in an included PHP file
            if (File::isValid($this->options->get('export'))) {

                // Include the file and check for the global variable ```$_cli_export```
                File::load($this->options->get('export'));
                if (isset($GLOBALS['_cli_export'])) {
                    $this->export =& ${$GLOBALS['_cli_export']};

                } else {
                    $msg = 'Missing $_cli_export variable in "' . $this->options->get('export') . '"!';
                    throw new BadMethodCallException($msg, 1364487850);
                }

            // Option #3 is to retrieve the result from a static variable (e.g. ```tx_myext_pi1::someConst```)
            // defined in the export command line option
            } else if (strpos($this->options->get('export'), '::')) {

                // Resolves the definition of the static variable
                $exports = Compatibility::trimExplode('::', $this->options->get('export'), true, 2);
                $class = $exports[0];
                $name  = $exports[1];

                // Access the static variable, even if it's private
                $property = new ReflectionProperty($class, $name);
                $property->setAccessible(true);
                $this->export = $property->getValue();

            // Option #4 is to set the result from an existing global, for example ```--export TYPO3_DB```.
            } else if (is_object($GLOBALS[$this->options->get('export')])) {
                $this->export = $GLOBALS[$this->options->get('export')];

            // Option #5 is to retrieve the result from the global scope similar to the TYPO3 getText functionality.
            // For example ```TSFE|id``` will retrieve the current page id.
            } else if (strpos($this->options->get('export'), '|')) {
                $this->export = Typo3::getGlobal($this->options->get('export'));

            // Option #6 is to assume the command line option ```--export``` defines a property of the current class.
            } else {
                $property = new ReflectionProperty($this->qlass->get(), $this->options->get('export'));
                $property->setAccessible(true);
                $this->export = $property->getValue($this->qlass->instance());
            }
        }
    }

    /**
     * # Return the Processed Result
     * Returns the processed result. If the result is an object creates a var_dump of the object.
     *
     * @return mixed
     */
    public function get()
    {
        if (is_object($this->export)) {
            return json_decode(json_encode($this->export), true);

        } else {
            return $this->export;
        }
    }

    /**
     * # Is Defined
     * Checks if an export option has been defined
     *
     * @return bool
     */
    public function has()
    {
        return $this->options->has('export');
    }
}

