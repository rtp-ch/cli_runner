<?php
namespace RTP\CliRunner\Service;

class Compatibility
{

    /**
     * Abstraction method which returns System Environment Variables regardless of server OS, CGI/MODULE version etc.
     * Basically this is SERVER variables for most of them.
     * This should be used instead of getEnv() and $_SERVER/ENV_VARS to get reliable values for all situations.
     *
     * @param string $getEnvName Name of the "environment variable"/"server variable" you wish to use.
     *        Valid values are SCRIPT_NAME, SCRIPT_FILENAME, REQUEST_URI, PATH_INFO, REMOTE_ADDR, REMOTE_HOST,
     *        HTTP_REFERER, HTTP_HOST, HTTP_USER_AGENT, HTTP_ACCEPT_LANGUAGE, QUERY_STRING, TYPO3_DOCUMENT_ROOT,
     *        TYPO3_HOST_ONLY, TYPO3_HOST_ONLY, TYPO3_REQUEST_HOST, TYPO3_REQUEST_URL, TYPO3_REQUEST_SCRIPT,
     *        TYPO3_REQUEST_DIR, TYPO3_SITE_URL, _ARRAY
     * @return string Value based on the input key, independent of server/os environment.
     */
    public static function getIndpEnv($getEnvName)
    {
        if (class_exists('\TYPO3\CMS\Core\Utility\GeneralUtility')) {
            return call_user_func(array('\TYPO3\CMS\Core\Utility\GeneralUtility', 'getIndpEnv'), $getEnvName);

        } else {
            return call_user_func(array('t3lib_div', 'getIndpEnv'), $getEnvName);
        }
    }


    /**
     * Returns the absolute filename of a relative reference, resolves the "EXT:" prefix (way of referring to files
     * inside extensions) and checks that the file is inside the PATH_site of the TYPO3 installation and implies a
     * check with \TYPO3\CMS\Core\Utility\GeneralUtility::validPathStr(). Returns FALSE if checks failed.
     * Does not check if the file exists.
     *
     * @param string $filename The input filename/filepath to evaluate
     * @param boolean $onlyRelative If $onlyRelative is set (which it is by default), then only return values relative
     *        to the current PATH_site is accepted.
     * @param boolean $relToTYPO3_mainDir If $relToTYPO3_mainDir is set, then relative paths are relative to PATH_typo3
     *        constant - otherwise (default) they are relative to PATH_site
     * @return string Returns the absolute filename of $filename IF valid, otherwise blank string.
     */
    public static function getFileAbsFileName($filename, $onlyRelative = true, $relToTYPO3_mainDir = false)
    {
        if (class_exists('\TYPO3\CMS\Core\Utility\GeneralUtility')) {
            return call_user_func(
                array('\TYPO3\CMS\Core\Utility\GeneralUtility', 'getFileAbsFileName'),
                $filename,
                $onlyRelative,
                $relToTYPO3_mainDir
            );

        } else {
            return call_user_func(
                array('t3lib_div', 'getFileAbsFileName'),
                $filename,
                $onlyRelative,
                $relToTYPO3_mainDir
            );
        }
    }

    /**
     * Creates an instance of a class taking into account the class-extensions
     * API of TYPO3. USE THIS method instead of the PHP "new" keyword.
     *
     * E.g. "$obj = new myclass;" should be:
     * "$obj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("myclass")"
     *
     * You can also pass arguments for a constructor:
     * \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('myClass', $arg1, $arg2, ..., $argN)
     *
     * @throws \InvalidArgumentException if classname is an empty string
     * @param string $class name of the class to instantiate, must not be empty
     * @return object the created instance
     */
    public static function makeInstance($class)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        array_unshift($arguments, $class);

        if (class_exists('\TYPO3\CMS\Core\Utility\GeneralUtility')) {
            return call_user_func_array(array('\TYPO3\CMS\Core\Utility\GeneralUtility', 'makeInstance'), $arguments);

        } else {
            return call_user_func_array(array('t3lib_div', 'makeInstance'), $arguments);
        }
    }

    /**
     * Explodes a string and trims all values for whitespace in the ends.
     * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
     * @see \t3lib_div::trimExplode
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode
     *
     * @param string $delimiter Delimiter string to explode with
     * @param string $str The string to explode
     * @param boolean $onlyNonEmptyValues If set (default), all empty values (='') will NOT be set in output
     * @param int $limit If positive, the result will contain a maximum of $limit elements, if negative,
     *        all components except the last -$limit are returned, if zero (default), the result is not limited at all.
     *
     * @return array
     */
    public static function trimExplode($delimiter, $str, $onlyNonEmptyValues = true, $limit = 0)
    {
        $arr = array();
        if (is_string($str)) {

            // Explodes and trims the array
            $arr = (array) self::trimList(explode($delimiter, $str), $onlyNonEmptyValues);

            // $limit cannot be larger than the number of array members
            $limit = (is_int($limit) && abs($limit) < count($arr)) ? $limit : 0;

            // Apply $limit to the array
            if ($limit > 0) {
                $arr =  array_slice($arr, 0, $limit);

            } elseif($limit < 0) {
                $arr = array_slice($arr, $limit);
            }
        }

        return $arr;
    }

    /**
     * Trims members of and optionally strips empty members from an array.
     *
     * @static
     * @param array $arr
     * @param boolean $onlyNonEmptyValues
     *
     * @return array
     */
    public static function trimList($arr, $onlyNonEmptyValues = true)
    {
        if ($onlyNonEmptyValues) {
            return array_filter(array_map('trim', $arr), 'strlen');

        } else {
            return array_map('trim', $arr);
        }
    }

    /**
     * Returns the absolute path to the extension with extension key $key
     * If the extension is not loaded the function will die with an error message
     * Useful for internal fileoperations
     *
     * @param $key string Extension key
     * @param $script string $script is appended to the output if set.
     * @throws \BadFunctionCallException
     * @return string
     */
    public static function extPath($key, $script = '')
    {
        if (class_exists('\TYPO3\CMS\Core\Utility\ExtensionManagementUtility')) {
            return call_user_func(
                array('\TYPO3\CMS\Core\Utility\ExtensionManagementUtility', 'extPath'),
                $key,
                $script
            );

        } else {
            return call_user_func(array('t3lib_extMgm', 'getIndpEnv'), $key, $script);
        }
    }
}

