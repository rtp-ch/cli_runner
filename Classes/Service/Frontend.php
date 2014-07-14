<?php
namespace RTP\CliRunner\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

/**
 * Simulates a frontend environment
 */
class Frontend
{
    /**
     * @var TypoScriptFrontendController
     */
    protected static $TSFE;

    /**
     * Simulates a frontend environment. Inspired by various hacks for simulating the frontend in
     * Tx_Fluid_ViewHelpers_CObjectViewHelper, Tx_Fluid_ViewHelpers_ImageViewHelper,
     * Tx_Fluid_ViewHelpers_Format_CropViewHelper, Tx_Fluid_ViewHelpers_Format_HtmlViewHelper and
     * Tx_Extbase_Utility_FrontendSimulator, Tx_Phpunit_Framework::createFakeFrontEnd (and possibly others...)
     *
     * @param int $pageId
     * @param int $noCache
     */
    public static function simulate($pageId = 0, $noCache = 0)
    {
        self::setTimeTracker();
        self::setTsfe($pageId, $noCache);
        self::setWorkingDir();
        self::setCharSet();
        self::setPageSelect();
        self::setTypoScript();
        self::setContentObject(array(), '');
    }

    /**
     * @param array  $data
     * @param string $table
     */
    private static function setContentObject(array $data = array(), $table = '')
    {
        self::$TSFE->cObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
        self::$TSFE->cObj->start($data, $table);
    }

    /**
     * Creates an instance of t3lib_pageSelect
     */
    private static function setPageSelect()
    {
        self::$TSFE->sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');

        self::$TSFE->sys_page->versioningPreview     = false;
        self::$TSFE->sys_page->versioningWorkspaceId = false;
        self::$TSFE->where_hid_del                   = ' AND pages.deleted=0';
        self::$TSFE->sys_page->init(false);
        self::$TSFE->sys_page->where_hid_del .= ' AND pages.doktype<200';
        self::$TSFE->sys_page->where_groupAccess =
            self::$TSFE->sys_page->getMultipleGroupsWhereClause('pages.fe_group', 'pages');
    }

    /**
     * Initializes TypoScript templating
     */
    private static function setTypoScript()
    {
        self::$TSFE->tmpl->runThroughTemplates(
            self::$TSFE->sys_page->getRootLine(self::$TSFE->id),
            0
        );
        self::$TSFE->tmpl->generateConfig();
        self::$TSFE->tmpl->loaded = 1;
        self::$TSFE->settingLanguage();
        self::$TSFE->settingLocale();
    }

    /**
     * Initializes global charset helpers
     */
    private static function setCharSet()
    {
        // preparing csConvObj
        if (!is_object(self::$TSFE->csConvObj)) {
            if (is_object($GLOBALS['LANG'])) {
                self::$TSFE->csConvObj = $GLOBALS['LANG']->csConvObj;

            } else {
                self::$TSFE->csConvObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Charset\\CharsetConverter');
            }
        }

        // preparing renderCharset
        if (!is_object(self::$TSFE->renderCharset)) {
            if (is_object($GLOBALS['LANG'])) {
                self::$TSFE->renderCharset = $GLOBALS['LANG']->charSet;

            } else {
                self::$TSFE->renderCharset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
            }
        }
    }

    /**
     * Resets the current working directory to the TYPO3 installation path
     */
    private static function setWorkingDir()
    {
        chdir(PATH_site);
    }

    /**
     * Sets a fake time tracker
     */
    private static function setTimeTracker()
    {
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = GeneralUtility::makeInstance('t3lib_TimeTrackNull');
        }
    }

    private static function initTSFE($pageId = 0, $noCache = 0)
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
            $GLOBALS['TYPO3_CONF_VARS'],
            $pageId,
            $noCache
        );
        // make a reference so we can be sure that any change in self::$TSFE affects $GLOBALS['TSFE']
        self::$TSFE      =& $GLOBALS['TSFE'];
    }

    /**
     * Sets self::$TSFE
     */
    private static function setTsfe($pageId = 0, $noCache = 0)
    {
        self::initTSFE($pageId, $noCache);
        self::$TSFE->beUserLogin         = false;
        self::$TSFE->cObjectDepthCounter = 100;
        self::$TSFE->workspacePreview    = '';
        self::$TSFE->initFEuser();
        self::$TSFE->determineId();
        self::$TSFE->initTemplate();
        self::$TSFE->config                     = array();
        self::$TSFE->tmpl->getFileName_backPath = PATH_site;
        self::$TSFE->baseUrl                    = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
    }
}

