<?php
namespace RTP\CliRunner\Command;

use BadMethodCallException;
use RTP\CliRunner\Utility\File as File;
use RTP\CliRunner\Utility\Typo3 as Typo3;
use RTP\CliRunner\Service\Compatibility as Compatibility;

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
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Checks if the class has been defined
     *
     * @return bool
     */
    public function has()
    {
        return (boolean) $this->get();
    }

    /**
     * Returns the class name
     *
     * @return mixed|string
     */
    public function name()
    {
        if (is_object($this->get())) {
            return get_class($this->get());

        } else {
            return $this->get();
        }
    }

    /**
     * Returns the class name or instance
     *
     * @return mixed
     */
    public function get()
    {
        return $this->qlass;
    }

    /**
     * Returns an instance of the class
     *
     * @return object
     */
    public function instance()
    {
        if (!is_object($this->get())) {
            $this->qlass = Compatibility::makeInstance($this->qlass);
        }

        return $this->get();
    }

    /**
     * Attempts to set the class name or instance from the arguments
     */
    public function set()
    {
        if (isset($GLOBALS['_cli_class'])) {
            // If the class has already been defined set it from the $__class variable
            $this->qlass =& $GLOBALS['_cli_class'];

        } elseif ($this->options->has('class')) {
            if (File::isValid($this->options->get('class'))) {
                // If the argument is a file then attempt to load the file. The class
                // should be included in a variable $__class in the file which is being loaded.
                // The $__class variable can be a string pointing to the class name or an instance
                // of the class.
                File::load($this->options->get('class'));

                // Must be exposed in a global variable called $__class.
                if (isset($__class)) {
                    $this->qlass =& $GLOBALS['_cli_class'];

                } else {
                    $msg = 'Missing global variable _cli_class variable in "' . $this->options->get('class') . '"!';
                    throw new BadMethodCallException($msg, 1364487850);
                }

            } elseif (is_object($GLOBALS[$this->options->get('class')])) {
                // If the argument points to a global, such as TSFE then use that as the class instance
                $this->qlass = $GLOBALS[$this->options->get('class')];

            } elseif (strpos($this->options->get('class'), '|')) {
                // Attempts to resolve the argument to a global variable when the input string defines array keys separated
                // by "|" Example: "TSFE|cObj" will return the value $GLOBALS['TSFE']->cObj
                // @see tslib_cObj::getGlobal
                $this->qlass = Typo3::getGlobal($this->options->get('class'));

            } else {
                // Finally the argument is assumed to the class name
                $this->qlass = $this->options->get('class');
            }
        }
    }
}

