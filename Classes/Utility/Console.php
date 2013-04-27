<?php
namespace RTP\RtpCli\Utility;

class Console
{
    /**
     * @var \RTP\RtpCli\Cli\Options
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
     * @param $text
     * @param string $signature
     */
    public function message($text, $signature = '')
    {
        if (strlen($signature)) {
            $message     = 'Output of "' . $signature . '" with arguments:';
        } else {
            $message     = 'Output of cli_runner with arguments:';
        }

        $border = str_repeat('=', strlen($message));
        echo "\n" . $border . "\n";
        echo $message . "\n";
        echo $border  . "\n\n";

        print_r($this->options->get());

        echo "\n" . $border . "\n\n";

        print_r($text);

        echo "\n\n" . $border . "\n";
        exit;
    }

    /**
     * Prints help message
     */
    public function help()
    {
        $help  = '...' . PHP_EOL;

        echo $help;
    }
}

