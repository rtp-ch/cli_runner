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
     * defined in the command line option "args". The PHP file must include a variable called "$__args"
     * which is an array that contains the arguments that will be passed to the method.
     *
     * @throws BadMethodCallException
     */
    public function set()
    {
        if ($this->options->has('args')) {

            // "args" must point to a PHP file which contains a variable $__args.
            if (File::isValid($this->options->get('args'))) {
                File::load($this->options->get('args'));

                if (isset($__args)) {
                    $this->arguments =& (array) $__args;

                } else {
                    $msg = 'Missing $__args variable in "' . $this->options->get('args') . '"!';
                    throw new BadMethodCallException($msg, 1364419871);
                }

            } else {
                $msg  = 'Invalid file type "' . $this->options->get('args') . '"!';
                $msg .= 'File must have one of the following extensions: ' . implode(',', File::getValidTypes());
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

