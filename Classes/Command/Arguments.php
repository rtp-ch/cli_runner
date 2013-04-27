<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;

class Arguments
{
    /**
     * @var string Name of the class
     */
    private $arguments = array();

    /**
     * @var \RTP\CliRunner\Cli\Options
     */
    private $options;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Sets the arguments for the method call. The arguments are loaded from a PHP file which is
     * defined in the command line option "args". The PHP file must include a global variable called "$_cli_arguments"
     * which is an array that contains the arguments that will be passed to the method.
     *
     * @throws BadMethodCallException
     */
    public function set()
    {
        // Checks if the global has already been defined.
        if (isset($GLOBALS['_cli_arguments'])) {
            $this->arguments =& $GLOBALS['_cli_arguments'];

        } else if ($this->options->has('args')) {
            // Loads any PHP file which has been defined in the command line option args
            if (File::isValid($this->options->get('args'))) {
                $args = File::load($this->options->get('args'));

                if (is_array($args)) {
                    // If the loaded file returned an array then that is assumed to be the arguments
                    $this->arguments = $args;

                } else if (isset($GLOBALS['_cli_arguments'])) {
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
     * Checks if any arguments have been set
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * Returns the array of arguments
     *
     * @return array
     */
    public function get()
    {
        return (is_array($this->arguments) && !empty($this->arguments)) ? $this->arguments : array();
    }
}

