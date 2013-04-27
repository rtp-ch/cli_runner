<?php
namespace RTP\CliRunner\Cli;

class Options
{
    /**
     * @var array
     */
    static private $allowed = array(
        '?' => 'help',
        'c' => 'class',
        'm' => 'method',
        'a' => 'args',
        'f' => 'file',
        'p' => 'page',
        'v' => 'env',
        'n' => 'no_cache',
        'e' => 'export',
    );

    /**
     * @var array
     */
    private $options = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        // Shifts of the first argument which is the name of the script
        array_shift($options);

        // Parses the cli arguments
        $options = array_chunk($options, 2);
        foreach ($options as $argument) {

            $option = strtolower(str_replace('-', '', $argument[0]));

            if (in_array($option, self::$allowed)) {
                $this->options[$option] = $argument[1];

            } elseif (isset(self::$allowed[$option])) {
                $this->options[self::$allowed[$option]] = $argument[1];
            }
        }
    }

    /**
     * Checks if the given command line argument was set
     *
     * @param $option
     * @return bool
     */
    public function has($option)
    {
        return isset($this->options[$option]);
    }

    /**
     * Retrieves the value for the given command line argument
     *
     * @param $option
     * @return string|null
     */
    public function get($option = null)
    {
        if (!is_null($option)) {
            return $this->has($option) ? $this->options[$option] : null;

        } else {
            return $this->options;
        }

    }
}

