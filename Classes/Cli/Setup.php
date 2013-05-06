<?php
namespace RTP\CliRunner\Cli;

use BadMethodCallException;
use RTP\CliRunner\Service\Compatibility;
use RTP\CliRunner\Utility\File as File;
use RTP\CliRunner\Utility\Method as Method;

/**
 * Class Arguments
 * @package RTP\CliRunner\Command
 */
class Setup
{
    /**
     * @var string Setup command
     */
    private $setup;

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
     * # Define Setup Operations
     * Sets the command which will be executed for any setup actions. The setup command can be defined as a
     * function/method reference, ```[file-reference":"]["&"]class/function["->"method-name]`` or
     * ```[file-reference":"]["&"]class/function["::"method-name]``` similar to ```t3lib_div::callUserFunction```
     *
     * If the command includes a file-reference that file will be loaded. For details on how the actual command can be
     * defined see ```RTP\CliRunner\Cli\Setup::Run``` below.
     *
     * @see t3lib_div::callUserFunction
     * @throws BadMethodCallException
     */
    public function set()
    {
        // Option #1 is if the global variable $_cli_setup has already been defined (e.g. from a PHP file which
        // has already been included).
        if (isset($GLOBALS['_cli_setup'])) {
            $this->setup = $GLOBALS['_cli_setup'];

        } elseif ($this->options->has('setup')) {
            // Option #2 is the command line option ```setup```
            $this->setup = $this->options->get('setup');
        }

        // If the setup operations includes a file reference the file is loaded and the reference is stripped from
        // the setup operation definition.
        if ($this->setup && strstr($this->setup, ':') !== false) {

            $setupParts = Compatibility::trimExplode(':', $this->options->get('setup'), true, 2);
            $setupFile = $setupParts[0];
            $this->setup = $setupParts[1];

            if (File::isValid($setupFile)) {
                File::load($setupFile);

            } else {
                $msg  = 'Invalid file type "' . $setupFile . '"! ';
                $msg .= 'File must have one of the following extensions: ' . implode(', ', File::getValidTypes());
                throw new BadMethodCallException($msg, 1367302170);
            }
        }

        // The persistence operator (&) as available in callUserFunction is not in effect so remove it if set.
        if (substr($this->setup, 0, 1) === '&') {
            $this->setup = substr($this->setup, 1);
        }
    }

    /**
     * # Run Setup Operations
     * Executes the defined setup operations. The setup command can be defined in any of the following ways:
     * - if defined as ```class->method``` the method will be called either statically or on an instance of the class
     *   (depending on hwo the method is actually defined).
     * - if there is no operator (->) and the command is callable then it is executed directly
     * - and finally, if there is no operator (->) and the command is not callable then it  is assumed to be a class
     *   and the method ```setUp``` of that class is executed.
     *
     * @see t3lib_div::callUserFunction
     * @throws \BadMethodCallException
     */
    public function run()
    {
        if ($this->has()) {
            if (strstr($this->setup, Method::ARROW_OPERATOR) !== false) {
                $setupParts = Compatibility::trimExplode(Method::ARROW_OPERATOR, $this->setup, true, 2);
                Method::execute($setupParts[1], array(), $setupParts[0]);

            } elseif (is_callable($this->setup)) {
                Method::execute($this->setup);

            } elseif (Method::methodExists('setUp', $this->setup)) {
                Method::execute('setUp', array(), $this->setup);

            } else {
                $msg  = 'Cannot resolve a valid setup action from "' . $this->setup . '"!';
                throw new BadMethodCallException($msg, 1367309051);
            }
        }
    }

    /**
     * # Is Defined
     * Checks if any setup opertaions have been defined
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * # Setup Operations Definition
     *
     * @return string
     */
    public function get()
    {
        return $this->setup;
    }
}

