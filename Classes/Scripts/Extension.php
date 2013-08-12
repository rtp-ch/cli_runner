<?php
namespace RTP\CliRunner\Scripts;

use RTP\CliRunner\Service\Compatibility;

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
     * Required to satisfy TYPO3's parentObj pattern
     * @var \tx_em_Extensions_List
     */
    public $extensionList;

    /**
     * Install an extension
     *
     * @param string $extKey Name of the extension to install.
     * @return bool
     */
    public function install($extKey)
    {
        if (self::isLoaded($extKey)) {
            return true;
        }

        if (class_exists('TYPO3\CMS\Extensionmanager\Utility\InstallUtility')) {

            // @see tx_introduction_import_extension::enableExtension
            /** @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager */
            $objectManager = Compatibility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            /** @var $installUtility \TYPO3\CMS\Extensionmanager\Utility\InstallUtility */
            $installUtility = $objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility');
            $installUtility->install($extKey);

        } else {
            $this->extensionList = Compatibility::makeInstance('tx_em_Extensions_List');
            list($inst_list,) = $this->extensionList->getInstalledExtensions();
            $newExtList = $this->extensionList->addExtToList($extKey, $inst_list);

            $install = Compatibility::makeInstance('tx_em_Install', $this);
            $install->setSilentMode(true);
            $install->writeNewExtensionList($newExtList);

            \tx_em_Tools::refreshGlobalExtList();
            $install->forceDBupdates($extKey, $inst_list[$extKey]);
        }
    }

    /**
     * Check if an extension is loaded
     *
     * @param string $extKey Name of the extension to install.
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