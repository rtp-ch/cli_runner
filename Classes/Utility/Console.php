<?php
namespace RTP\CliRunner\Utility;

class Console
{

    /**
     * # Print a Message
     *
     * @param mixed $output
     * @param array $arguments
     * @param string $signature
     * @param string $documentation
     * @param mixed $debug
     */
    public static function message(
        $output,
        array $arguments = array(),
        $signature = 'cli_runner',
        $documentation = '',
        $debug
    ) {
        $headline = 'Output of "' . $signature . '" with arguments';
        $border = str_repeat('=', strlen($headline));

        self::border($border);
        echo $headline;
        self::border($border);

        if ($documentation) {
            self::subheadline('Description:');
            echo preg_replace('/^\s+/im', ' ', $documentation);
            self::border($border);
        }

        self::subheadline('Arguments:');
        self::dump($arguments);
        self::border($border);

        self::subheadline('Output:');
        self::dump($output);
        self::border($border);

        if ($debug) {
            self::subheadline('Debug:');
            self::dump($debug);
            self::border($border);
        }

        exit;
    }

    /**
     * # Dump Variable
     * var_dumps a variable
     *
     * @param $variable
     */
    private static function dump($variable)
    {
        if (is_scalar($variable)) {
            echo $variable . PHP_EOL;

        } elseif (is_array($variable)) {
            print_r($variable);

        } elseif (is_object($variable)) {
            print_r(json_decode(json_encode($variable)), true);
        }
    }

    /**
     * # Subheadline
     * Prints an underlined heading
     *
     * @param $subheadline
     */
    private static function subheadline($subheadline)
    {
        echo PHP_EOL . $subheadline . PHP_EOL;
        echo str_repeat('-', strlen($subheadline)) . PHP_EOL . PHP_EOL;
    }

    /**
     * # Border
     * Prints a line of "="s
     *
     * @param $border
     */
    private static function border($border)
    {
        echo PHP_EOL . $border . PHP_EOL;
    }

    /**
     * Prints help message
     */
    public static function help()
    {
        $help  = '...' . PHP_EOL;

        echo $help;
    }
}

