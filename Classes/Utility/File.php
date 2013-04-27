<?php
namespace RTP\CliRunner\Utility;

use BadMethodCallException;
use RTP\CliRunner\Service\Compatibility as Compatibility;

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
     * Includes the given file.
     *
     * @param $file
     * @return mixed
     */
    public static function load($file)
    {
        return require_once self::getPath($file);
    }

    /**
     * If the incoming string has a file extension which matches any of the valid file types
     * then the argument is considered to be a file.
     *
     * @param $file
     * @return bool
     */
    public static function isValid($file)
    {
        $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($fileExt, self::$validFileTypes);
    }

    /**
     * Returns the list of valid file types
     *
     * @return array
     */
    public static function getValidTypes()
    {
        return self::$validFileTypes;
    }

    /**
     * Attempts to resolve the path to a file using a variety of strategies.
     *
     * @param $file
     * @return string
     * @throws BadMethodCallException
     */
    private static function getPath($file)
    {
        $path = $file;

        if (!is_file($file) || !is_readable($file)) {
            $path = Compatibility::getFileAbsFileName($file);
        }

        if (!is_file($path) || !is_readable($path)) {
            $path = Compatibility::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $file);
        }

        if (!is_file($path) || !is_readable($path)) {
            $msg = 'Unable to read file "' . $file . '"!';
            throw new BadMethodCallException($msg, 1360849885);
        }

        return $path;
    }
}

