<?php
namespace RTP\CliRunner\Utility;

use BadMethodCallException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class File
{
    /**
     * @var array List of allowed file types by extension
     */
    private static $validFileTypes = array(
        'php',
        'phtml',
        'inc'
    );

    /**
     * # Include a File
     *
     * @param $file
     * @return mixed
     */
    public static function load($file)
    {
        return require_once self::getPath($file);
    }

    /**
     * # Allowed File Type
     * Compares the file extension of a given file against the list of allowed file extensions
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
     * # List of Allowed File Types
     *
     * @return array
     */
    public static function getValidTypes()
    {
        return self::$validFileTypes;
    }

    /**
     * # Path to File
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
            $path = GeneralUtility::getFileAbsFileName($file);
        }

        if (!is_file($path) || !is_readable($path)) {
            $path = GeneralUtility::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $file);
        }

        if (!is_file($path) || !is_readable($path)) {
            $msg = 'Unable to read file "' . $file . '"!';
            throw new BadMethodCallException($msg, 1360849885);
        }

        return $path;
    }
}

