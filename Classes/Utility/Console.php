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
     */
    public static function message($output, array $arguments = array(), $signature = 'cli_runner', $documentation = '')
    {
        $headline = 'Output of "' . $signature . '" with arguments';
        $border = str_repeat('=', strlen($headline));

        self::border($border);
        echo $headline;
        self::border($border);

        if ($documentation) {
            self::subheadline('Description:');
            echo $result = preg_replace('/^\s+/im', ' ', $documentation);
            self::border($border);
        }

        self::subheadline('Arguments:');
        print_r($arguments);
        self::border($border);

        self::subheadline('Output:');
        print_r($output);
        self::border($border);
        
        exit;
    }

    private static function subheadline($subheadline)
    {
        echo PHP_EOL . $subheadline . PHP_EOL;
        echo str_repeat('-', strlen($subheadline)) . PHP_EOL . PHP_EOL;
    }

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

