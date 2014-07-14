<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Cli\Options;
use RTP\CliRunner\Utility\File;

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
     * @var Options
     */
    private $options;

    /**
     * # Constructor
     * Gets an instance of the command line options.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
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
        // **Option #1** is if the global variable $_cli_method has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_method'])) {
            $this->method = (string) $GLOBALS['_cli_method'];

        } elseif ($this->options->has('method')) {

            // **Option #2** is to include a PHP file which defines the method
            if (File::isValid($this->options->get('method'))) {

                // **Option #2a** is to return string (the method name) from the included PHP file
                $method = File::load($this->options->get('method'));
                if (is_string($method)) {
                    $this->method = $method;

                } elseif (isset($GLOBALS['_cli_method'])) {
                    // **Option #2b** is to set the global variable _cli_method in the included PHP file
                    $this->method = (string) $GLOBALS['_cli_method'];

                } else {
                    $msg = 'Missing $__method variable in "' . $this->options->get('method') . '"!';
                    throw new BadMethodCallException($msg, 1366567236);
                }

            } else {
                // **Option #3** is when the command line option is the method name.
                $this->method = $this->options->get('method');
            }
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
}

