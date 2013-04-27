<?php
namespace RTP\RtpCli;

use BadMethodCallException;
use t3lib_div;
use t3lib_extMgm;

class File
{
    /**
     * @var array
     */
    private static $validFileTypes = array(
        'php',
        'phtml',
        'inc'
    );

    /**
     * @param $file
     */
    public static function load($file)
    {
        require_once self::getPath($file);
    }

    /**
     * @param $file
     * @return bool
     */
    public static function isPhp($file)
    {
        $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($fileExt, self::$validFileTypes);
    }

    /**
     * @param $file
     * @return string
     * @throws BadMethodCallException
     */
    private static function getPath($file)
    {
        $path = $file;

        if (!is_file($file) || !is_readable($file)) {
            $path = t3lib_div::getFileAbsFileName($file);
        }

        if (!is_file($path) || !is_readable($path)) {
            $path = t3lib_div::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $file);
        }

        if (!is_file($path) || !is_readable($path)) {
            $msg = 'Unable to read file "' . $file . '"!';
            throw new BadMethodCallException($msg, 1360849885);
        }

        return $path;
    }
}

