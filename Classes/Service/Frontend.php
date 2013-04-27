<?php
namespace RTP\CliRunner\Service;

use RTP\CliRunner\Service\Compatibility as Compatibility;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

/**
 * Simulates a frontend environment
 */
class Frontend
{

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
     * @param array $data
     * @param string $table
     */
    private static function setContentObject(array $data = array(), $table = '')
    {
        $GLOBALS['TSFE']->cObj = Compatibility::makeInstance('tslib_cObj');
        $GLOBALS['TSFE']->cObj->start($data, $table);
    }

    /**
     * Creates an instance of t3lib_pageSelect
     */
    private static function setPageSelect()
    {
        $GLOBALS['TSFE']->sys_page = Compatibility::makeInstance('t3lib_pageSelect');
        $GLOBALS['TSFE']->sys_page->versioningPreview = false;
        $GLOBALS['TSFE']->sys_page->versioningWorkspaceId = false;
        $GLOBALS['TSFE']->where_hid_del = ' AND pages.deleted=0';
        $GLOBALS['TSFE']->sys_page->init(false);
        $GLOBALS['TSFE']->sys_page->where_hid_del .= ' AND pages.doktype<200';
        $GLOBALS['TSFE']->sys_page->where_groupAccess =
            $GLOBALS['TSFE']->sys_page->getMultipleGroupsWhereClause('pages.fe_group', 'pages');
    }

    /**
     * Initializes TypoScript templating
     */
    private static function setTypoScript()
    {
        $GLOBALS['TSFE']->tmpl->runThroughTemplates(
            $GLOBALS['TSFE']->sys_page->getRootLine($GLOBALS['TSFE']->id),
            0
        );
        $GLOBALS['TSFE']->tmpl->generateConfig();
        $GLOBALS['TSFE']->tmpl->loaded = 1;
        $GLOBALS['TSFE']->settingLanguage();
        $GLOBALS['TSFE']->settingLocale();
    }

    /**
     * Initializes global charset helpers
     */
    private static function setCharSet()
    {
        // preparing csConvObj
        if (!is_object($GLOBALS['TSFE']->csConvObj)) {
            if (is_object($GLOBALS['LANG'])) {
                $GLOBALS['TSFE']->csConvObj = $GLOBALS['LANG']->csConvObj;

            } else {
                $GLOBALS['TSFE']->csConvObj = Compatibility::makeInstance('t3lib_cs');
            }
        }

        // preparing renderCharset
        if (!is_object($GLOBALS['TSFE']->renderCharset)) {
            if (is_object($GLOBALS['LANG'])) {
                $GLOBALS['TSFE']->renderCharset = $GLOBALS['LANG']->charSet;

            } else {
                $GLOBALS['TSFE']->renderCharset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
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
            $GLOBALS['TT'] = Compatibility::makeInstance('t3lib_TimeTrackNull');
        }
    }

    /**
     * Sets $GLOBALS['TSFE']
     */
    private static function setTsfe($pageId = 0, $noCache = 0)
    {
        $GLOBALS['TSFE'] = Compatibility::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $pageId, $noCache);
        $GLOBALS['TSFE']->beUserLogin = false;
        $GLOBALS['TSFE']->cObjectDepthCounter = 100;
        $GLOBALS['TSFE']->workspacePreview = '';
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->config = array();
        $GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
        $GLOBALS['TSFE']->baseUrl = Compatibility::getIndpEnv('TYPO3_SITE_URL');
    }
}

