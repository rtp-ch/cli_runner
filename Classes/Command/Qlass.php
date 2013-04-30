<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;
use RTP\CliRunner\Utility\Typo3 as Typo3;
use RTP\CliRunner\Service\Compatibility as Compatibility;

/**
 * Class Qlass
 * @package RTP\CliRunner\Command
 */
class Qlass
{
    /**
     * @var string|object Name or instance of the class
     */
    private $qlass;

    /**
     * @var \RTP\CliRunner\Cli\Options
     */
    private $options;

    /**
     * # Constructor
     * Gets an instance of the command line options handler
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * # Has Class
     * Checks for availability of a class
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * # Class
     * Returns the class, either it's name or an instance
     *
     * @return string|object
     */
    public function get()
    {
        return $this->qlass;
    }

    /**
     * # Set Class Name or Instance
     * Sets the name or instance of the class from the command line options. The command line option ```--class```
     * can either define any of the following:
     * - The name of the class, e.g. ```tx_meyext_pi1```
     * - The path to a PHP file which, when included can do any of the following:
     *      - Return the class name
     *      - Return an instance of the class
     *      - Define a global variable ```$_cli_class``` which contains the name or an instance of the class.
     * - An instance of a global class such as ```$GLOBALS['TYPO3_DB']```
     * - An instance of a class from the global scope, such as ```$GLOBALS['TSFE']->sys_page```
     *
     * @throws BadMethodCallException
     */
    public function set()
    {
        // **Option #1** is if the global variable $_cli_class has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_class'])) {
            $this->qlass =& $GLOBALS['_cli_class'];

        } elseif ($this->options->has('class')) {

            // **Option #2** is to include a PHP file which defines the class
            if (File::isValid($this->options->get('class'))) {

                // **Option #2a** is to return the class as a string (class name) or object (class instance) from
                // the included PHP file
                $class = File::load($this->options->get('class'));
                if (is_string($class) || is_object($class)) {
                    $this->qlass = $class;

                } elseif (isset($GLOBALS['_cli_class'])) {
                    // **Option #2b** is to define a global variable ```$_cli_class``` in the included PHP file.
                    $this->qlass =& $GLOBALS['_cli_class'];

                } else {
                    $msg = 'Missing global variable _cli_class variable in "' . $this->options->get('class') . '"!';
                    throw new BadMethodCallException($msg, 1364487850);
                }

            } elseif (is_object($GLOBALS[$this->options->get('class')])) {
                // **Option #3** is to set the class from an existing global, for example ```--class TYPO3_DB```.
                $this->qlass =& $GLOBALS[$this->options->get('class')];

            } elseif (strpos($this->options->get('class'), '|')) {
                // **Option #4** is to set retrieve a class from the global scope similar to the TYPO3 getText
                // functionality. For example ```TSFE|sys_page``` will retrieve the current instance of
                // ```$GLOBALS['TSFE']->sys_page``` (i.e. the global instance of t3lib_pageSelect).
                $this->qlass =& Typo3::getGlobal($this->options->get('class'));

            } else {
                // **Option #5** is when the command line option is the class name.
                $this->qlass = $this->options->get('class');
            }
        }
    }
}

