<?php
namespace RTP\CliRunner\Utility;

use RTP\CliRunner\Service\Compatibility;

/**
 * Class Qlass
 * @package RTP\CliRunner\Utility
 */
class Qlass
{
    /**
     * # Class Name
     * Returns the class name
     *
     * @param $class
     * @return string
     */
    public static function getName($class)
    {
        if (is_object($class)) {
            return get_class($class);

        } else {
            return $class;
        }
    }

    /**
     * # Class Instance
     * Returns an instance of the class, instantiates the class if no previous instance is available
     *
     * @param $class
     * @return object
     */
    public static function getInstance($class)
    {
        if (!is_object($class)) {
            $class = Compatibility::makeInstance($class);
        }

        return $class;
    }
}

