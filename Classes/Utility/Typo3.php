<?php
namespace RTP\CliRunner\Utility;

class Typo3
{

    /**
     * # Global Variable
     * Return global variable where the input string $var defines array keys separated by "|"
     * Example: $var = "HTTP_SERVER_VARS | something" will return
     * the value $GLOBALS['HTTP_SERVER_VARS']['something'] value
     * @see tslib_cObj::getGlobal()
     *
     * @param string $keyString Global var key, eg. "HTTP_GET_VAR" or "HTTP_GET_VARS|id" to get
     *        the GET parameter "id" back.
     * @return mixed Whatever value. If none, then false.
     */
    public static function getGlobal($keyString)
    {
        $keys = explode('|', $keyString);
        $numberOfLevels = count($keys);

        $rootKey = trim($keys[0]);
        $value = $GLOBALS[$rootKey];

        for ($i = 1; $i < $numberOfLevels && isset($value); $i++) {

            $currentKey = trim($keys[$i]);

            if (is_object($value)) {
                $value = $value->{$currentKey};

            } elseif (is_array($value)) {
                $value = $value[$currentKey];

            } else {
                $value = false;
                break;
            }
        }

        return $value;
    }
}