<?php

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

class rtp_cli
    extends t3lib_cli
{
    /**
     * @var
     */
    private $method;

    /**
     * @var
     */
    private $class;

    /**
     * @var
     */
    private $static;

    /**
     * @var
     */
    private $args;

    /**
     * @var array
     */
    static private $options = array(
        'c' => 'class',
        'm' => 'method',
        'a' => 'args',
        'f' => 'file'
    );

    /**
     * @var array
     */
    private $arguments = array();

    /**
     * @param $argv
     * @throws BadMethodCallException
     */
    public function cli_main($argv)
    {
        $opts   = $argv;
        $script = array_shift($opts);

        // Prints the help message if requested
        if (in_array('-?', $opts) || in_array('--help', $opts)) {
            $this->printHelp();
            exit;
        }

        // Parses the cli arguments
        $opts = array_chunk($opts, 2);
        foreach ($opts as $opt) {

            $option = strtolower(str_replace('-', '', $opt[0]));

            if (in_array($option, self::$options)) {
                $this->arguments[$option] = $opt[1];

            } elseif (isset(self::$options[$option])) {
                $this->arguments[self::$options[$option]] = $opt[1];
            }
        }

        //
        try {
            $this->includeFile();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setClass();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setMethod();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        //
        try {
            $this->setArgs();

        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
            $this->printMsg($result);
        }

        // TODO: Alternate Environments, e.g. backend
        if (!isset($this->arguments['env'])
            || strtolower($this->arguments['env']) === 'fe'
            || strtolower($this->arguments['env']) === 'frontend') {

            FrontendEnvironment::simulate();
        }

        //
        try {
            if ($this->hasClass()) {
                $method = new ReflectionMethod($this->getClass(), $this->getMethod());
                $method->setAccessible(true);

                if ($this->isStatic()) {
                    $result = $method->invokeArgs(null, $this->getArgs());

                } else {
                    $result = $method->invokeArgs(t3lib_div::makeInstance($this->getClass()), $this->getArgs());
                }

            } else {
                $result = call_user_func_array($this->getMethod(), $this->getArgs());
            }


        } catch (Exception $e) {
            $result = 'Exception #' . $e->getCode() . ': ' . $e->getMessage();
        }

        //
        $this->printMsg($result);
    }

    /**
     * @param $result
     */
    private function printMsg($result)
    {
        $methodCall  = $this->hasClass() ? $this->getClass() . $this->getMethodCall() : '';
        $methodCall .= $this->getMethod();
        $message     = 'Output of "' . $methodCall . '" with arguments:';
        $border      = str_repeat('=', strlen($message));

        echo "\n" . $border . "\n";
        echo $message . "\n";
        echo $border  . "\n\n";

        print_r($this->getArgs());

        echo "\n" . $border . "\n\n";

        print_r($result);

        echo "\n\n" . $border . "\n";
        exit;
    }

    /**
     * Prints help message
     */
    private function printHelp()
    {
        $help  = '...' . PHP_EOL;

        echo $help;
    }

    /**
     * @throws BadMethodCallException
     */
    private function setClass()
    {
        $this->class = $this->arguments['class'];
    }

    /**
     * @return mixed
     */
    private function getClass()
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    private function hasClass()
    {
        return (boolean) $this->class;
    }

    /**
     * @return string
     */
    private function getMethodCall()
    {
        return $this->isStatic() ? '::' : '->';
    }

    /**
     * @throws BadMethodCallException
     */
    private function setMethod()
    {
        $this->method = $this->arguments['method'];
        if (!$this->method) {
            $msg = 'Missing required method name!';
            throw new BadMethodCallException($msg, 1354959022);
        }

        if ($this->hasClass()) {
            $reflectionClass = new ReflectionClass($this->getClass());
            if (!$reflectionClass->hasMethod($this->getMethod())) {
                $msg = 'Method "' . $this->method . '" not available in class "' . $this->class . '"!';
                throw new BadMethodCallException($msg, 1354959172);
            }

        } elseif (!$this->hasClass() && !function_exists($this->getMethod())) {
            $msg = 'Unknown function "' . $this->getMethod() . '" or missing required class name!';
            throw new BadMethodCallException($msg, 1354958084);
        }
    }

    /**
     * @return mixed
     */
    private function getMethod()
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    private function isStatic()
    {
        if (is_null($this->static)) {
            $this->static = false;

            if ($this->hasClass()) {
                $method = new ReflectionMethod($this->getClass(), $this->getMethod());

                if ($method->isStatic()) {
                    $this->static = true;
                }
            }
        }

        return $this->static;
    }

    /**
     *
     */
    private function setArgs()
    {
        if ($this->arguments['args']) {
            $file = $this->arguments['args'];

            if ($file) {
                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName($this->arguments['args']);
                }

                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $this->arguments['args']);
                }

                if (is_file($file) && is_readable($file)) {
                    $this->args = json_decode(file_get_contents($file), true);

                } else {
                    $msg = 'Unable to read file "' . $this->arguments['args'] . '"';
                    throw new BadMethodCallException($msg, 1354965903);
                }
            }
        }
    }

    /**
     * @return mixed
     */
    private function getArgs()
    {
        return is_array($this->args) && !empty($this->args) ? $this->args : array();
    }

    /**
     * @throws BadMethodCallException
     */
    private function includeFile()
    {
        if ($this->arguments['file']) {
            $file = $this->arguments['file'];

            if ($file) {
                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName($this->arguments['file']);
                }

                if (!is_file($file) || !is_readable($file)) {
                    $file = t3lib_div::getFileAbsFileName(__DIR__ . DIRECTORY_SEPARATOR . $this->arguments['file']);
                }

                if (is_file($file) && is_readable($file)) {
                    require_once $file;

                } else {
                    $msg = 'Unable to include file "' . $this->arguments['file'] . '"';
                    throw new BadMethodCallException($msg, 1360849885);
                }
            }
        }
    }
}

/**
 * Simulates frontend environment by
 */
class FrontendEnvironment
{

    /**
     * Simulates a frontend environment. Inspired by various hacks for simulating the frontend in
     * Tx_Fluid_ViewHelpers_CObjectViewHelper, Tx_Fluid_ViewHelpers_ImageViewHelper,
     * Tx_Fluid_ViewHelpers_Format_CropViewHelper, Tx_Fluid_ViewHelpers_Format_HtmlViewHelper and
     * Tx_Extbase_Utility_FrontendSimulator (and possibly others...)
     *
     * @param array $data
     * @param string $table
     */
    public static function simulate(array $data = array(), $table = '')
    {
        self::setTimeTracker();
        self::setTsfe();
        self::setWorkingDir();
        self::setCharSet();
        self::setPageSelect();
        self::setTypoScript();
        self::setContentObject($data, $table);
    }

    /**
     * @param array $data
     * @param string $table
     */
    private static function setContentObject(array $data = array(), $table = '')
    {
        $GLOBALS['TSFE']->cObj = t3lib_div::makeInstance('tslib_cObj');
        $GLOBALS['TSFE']->cObj->start($data, $table);
    }

    /**
     * Creates an instance of t3lib_pageSelect
     */
    private static function setPageSelect()
    {
        $GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
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
        $typoScriptSetup = array();
        $template = t3lib_div::makeInstance('t3lib_TStemplate');
        $template->tt_track = 0;
        $template->init();
        $template->getFileName_backPath = PATH_site;
        $GLOBALS['TSFE']->tmpl = $template;
        $GLOBALS['TSFE']->tmpl->setup = $typoScriptSetup;
        $GLOBALS['TSFE']->config = $typoScriptSetup;
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
                $GLOBALS['TSFE']->csConvObj = t3lib_div::makeInstance('t3lib_cs');
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
            $GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_TimeTrackNull');
        }
    }

    /**
     * Sets $GLOBALS['TSFE']
     */
    private static function setTsfe()
    {
        // TODO: Third param allows setting the current page id
        // TODO: Fourth param allows setting cache / no_cache
        $GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
        // $GLOBALS['TSFE']  = new stdClass();
        $GLOBALS['TSFE']->cObjectDepthCounter = 100;
        $GLOBALS['TSFE']->baseUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
    }
}


$cliObj = t3lib_div::makeInstance('rtp_cli');
$cliObj->cli_main($_SERVER['argv']);

