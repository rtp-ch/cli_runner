<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;

/**
 * Class Arguments
 * @package RTP\CliRunner\Command
 */
class Arguments
{
    /**
     * @var array Arguments for the method
     */
    private $arguments = array();

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
     * # Set Method Arguments
     * Sets the arguments for the method call from the command line options. The arguments are loaded from
     * a PHP file which is defined in the command line option "args". The PHP file must include a **global**
     * variable called ```$_cli_arguments``` which is an array that contains the arguments that will be passed
     * to the method. For example:
     *
     *      $GLOBALS['_cli_arguments'] = array('argument1', 2);
     *
     * @throws BadMethodCallException
     */
    public function set()
    {
        // Option #1 is if the global variable $_cli_arguments has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_arguments'])) {
            $this->arguments =& $GLOBALS['_cli_arguments'];

        } elseif ($this->options->has('args')) {

            // Option #2 is to include a PHP file which defines the arguments
            if (File::isValid($this->options->get('args'))) {

                // Option #1 is to return an array from the included PHP file
                $arguments = File::load($this->options->get('args'));
                if (is_array($arguments)) {
                    $this->arguments = $arguments;

                } elseif (isset($GLOBALS['_cli_arguments'])) {
                    // Option #2 is to set the global variable _cli_arguments in the included PHP file
                    $this->arguments =& $GLOBALS['_cli_arguments'];

                } else {
                    $msg = 'Could not retrieve method arguments from "' . $this->options->get('args') . '". ';
                    throw new BadMethodCallException($msg, 1364419871);
                }

            } else {
                $msg  = 'Invalid file type "' . $this->options->get('args') . '"! ';
                $msg .= 'File must have one of the following extensions: ' . implode(', ', File::getValidTypes());
                throw new BadMethodCallException($msg, 1364419931);
            }
        }
    }

    /**
     * # Has Arguments
     * Checks for availability of arguments
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * # Method Arguments
     * Returns the array of arguments (or an empty array)
     *
     * @return array
     */
    public function get()
    {
        return (is_array($this->arguments) && !empty($this->arguments)) ? $this->arguments : array();
    }
}

