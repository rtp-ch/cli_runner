<?php
namespace RTP\RtpCli\Command;

use BadMethodCallException;
use RTP\RtpCli\Utility\File as File;
use RTP\RtpCli\Service\Compatibility as Compatibility;

class Export
{
    /**
     * @var mixed
     */
    private $export;

    /**
     * @var \RTP\RtpCli\Command\Qlass
     */
    private $qlass;

    /**
     * @var \RTP\RtpCli\Cli\Options
     */
    private $options;

    /**
     * @param $options
     * @param $qlass
     * @param $result
     */
    public function __construct($options, $qlass, $result)
    {
        $this->options = $options;
        $this->qlass = $qlass;
        $this->export = $result;
    }

    /**
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
     * @throws \BadMethodCallException
     */
    public function set()
    {
        if ($this->options->has('export')) {

            // Check for an existing $__export variable
            if (isset($__export)) {
                $this->export = ${$__export};

            } elseif (File::isValid($this->options->get('export'))) {

                // Attempts to retrieve a variable from an included file
                File::load($this->options->get('export'));

                // The variable must be exposed in a variable called $__export
                if (isset($__export)) {
                    $this->export = ${$__export};

                } else {
                    $msg = 'Missing $__export variable in "' . $this->options->get('export') . '"!';
                    throw new BadMethodCallException($msg, 1364487850);
                }

            } elseif (strpos($this->options['export'], '::')) {

                // Attempts to resolve a static variable
                $exports = Compatibility::trimExplode('::', $this->options->get('export'), true, 2);
                $class = $exports[0];
                $name  = $exports[1];

                $property = new ReflectionProperty($class, $name);
                $property->setAccessible(true);
                $this->export = $property->getValue();

            } elseif (is_object($GLOBALS[$this->options->get('export')])) {

                // Attempts to resolve a global variable
                $this->export = $GLOBALS[$this->options->get('export')];

            } elseif (strpos($this->options->get('export'), '|')) {
                $this->export = Utility::getGlobal($this->options->get('export'));

            } else {
                $property = new ReflectionProperty($this->qlass->get(), $this->options->get('export'));
                $property->setAccessible(true);
                $this->export = $property->getValue($this->qlass->instance());
            }
        }
    }

    /**
     * @return bool
     */
    public function has()
    {
        return $this->options->has('export');
    }
}

