<?php
namespace RTP\CliRunner\Scripts;

/**
 * #Provides command line access to extensions management.
 *
 * ##Install an extension:
 * ```typo3/cli_dispatch.phpsh cli_runner --class "\RTP\CliRunner\Scripts\Extension" --method install --args extKey```
 *
 * @package RTP\CliRunner
 * @author  Simon Tuck <stu@rtp.ch>
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
class Extension
{
    /**
     * Check if an extension is loaded
     *
     * @param string $extKey Name of the extension to install.
     *
     * @return mixed
     */
    private static function isLoaded($extKey)
    {
        if (class_exists('\TYPO3\CMS\Core\Utility\ExtensionManagementUtility')) {
            return call_user_func(
                array(
                    '\TYPO3\CMS\Core\Utility\ExtensionManagementUtility',
                    'isLoaded'
                ),
                $extKey
            );
        } else {
            return call_user_func(array('t3lib_extMgm', 'isLoaded'), $extKey);
        }
    }
}
