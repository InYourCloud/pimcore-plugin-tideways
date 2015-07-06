<?php
/**
 * Tideways Pimcore Plugin
 */

namespace Tideways;

use Pimcore\API\Plugin as PluginLib;

/**
 * Class Plugin
 *
 * @package Tideways
 */
class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    const SAMPLE_CONFIG_XML = "/Tideways/tideways.xml";
    const CONFIG_XML = '/var/config/tideways.xml';

    /**
     * @var bool store enabled state - set in config xml
     */
    private static $isEnabled = false;

    /**
     * Initialize Plugin
     *
     * Sets up Tideways, watchers, apiKey, various config options
     */
    public function init()
    {
        parent::init();

        if (!@class_exists('Tideways\Profiler')) {
            return;
        }

        if (!self::isInstalled()) {
            return;
        }

        $config = new \Zend_Config_Xml(self::getConfigName());

        self::$isEnabled = ($config->tideways->get('enabled', '0') == '1');
        if (!self::$isEnabled) {
            return;
        }

        $apiKey = $config->tideways->get('apiKey', '');
        if ($apiKey == '') {
            return;
        }

        // exclude pimcore backend traces?
        if (!\Pimcore\Tool::isFrontend() && ($config->tideways->get('excludeBackend', '1') == '1')) {
            return;
        }

        \Tideways\Profiler::detectFramework(\Tideways\Profiler::FRAMEWORK_ZEND_FRAMEWORK1);

        $sampleRate = (int)$config->tideways->get('sampleRate', 10);
        \Tideways\Profiler::start($apiKey, $sampleRate);

        foreach ($config->tideways->watchers->watcher as $watcher) {
            \Tideways\Profiler::watch($watcher);
        }

        self::$isEnabled = true;

    }

    /**
     * Install plugin
     *
     * Copies sample XML to website config path if it does not exist yet.
     * Sets config file parameter "installed" to 1 and "enabled" to "1"
     *
     * @return string install success|failure message
     */
    public static function install()
    {
        if (!file_exists(self::getConfigName())) {

            $defaultConfig = new \Zend_Config_Xml(PIMCORE_PLUGINS_PATH . self::SAMPLE_CONFIG_XML);
            $configWriter = new \Zend_Config_Writer_Xml();
            $configWriter->setConfig($defaultConfig);
            $configWriter->write(self::getConfigName());
        }

        $config = new \Zend_Config_Xml(self::getConfigName(), null, array('allowModifications' => true));
        $config->tideways->installed = 1;

        $configWriter = new \Zend_Config_Writer_Xml();
        $configWriter->setConfig($config);
        $configWriter->write(self::getConfigName());

        if (self::isInstalled()) {
            return "Successfully installed.";
        } else {
            return "Could not be installed";
        }
    }

    /**
     * Uninstall plugin
     *
     * Sets config file parameter "installed" to 0 (if config file exists)
     *
     * @return string uninstall success|failure message
     */
    public static function uninstall()
    {
        if (file_exists(self::getConfigName())) {

            $config = new \Zend_Config_Xml(self::getConfigName(), null, array('allowModifications' => true));
            $config->tideways->installed = 0;

            $configWriter = new \Zend_Config_Writer_Xml();
            $configWriter->setConfig($config);
            $configWriter->write(self::getConfigName());
        }

        if (!self::isInstalled()) {
            return "Successfully uninstalled.";
        } else {
            return "Could not be uninstalled";
        }
    }

    /**
     * Determine plugin install state
     *
     * @return bool true if plugin is installed (option "installed" is "1" in config file)
     */
    public static function isInstalled()
    {
        if (!file_exists(self::getConfigName())) {
            return false;
        }

        $config = new \Zend_Config_Xml(self::getConfigName());
        if ($config->tideways->installed != 1) {
            return false;
        }
        return true;
    }

    /**
     * Set custom variable for Tideways (only if installed & enabled)
     *
     * @param $var
     * @param $value
     */
    public static function setCustomVariable($var, $value)
    {
        if (self::$isEnabled) {
            \Tideways\Profiler::setCustomVariable($var, $value);
        }
    }

    /**
     * Set custom transaction name for Tideways (only if installed & enabled)
     *
     * @param $transactionName
     */
    public static function setTransactionName($transactionName)
    {
        if (self::$isEnabled) {
            \Tideways\Profiler::setTransactionName($transactionName);
        }
    }

    /**
     * Create Tideways event annotation via API
     *
     * @param $title
     * @param string $environment
     * @param string $type
     * @todo implement event creation
     */
    public static function createEvent($title, $environment='production', $type='release')
    {
        //curl -d '{"apiKey": "api key here", "name": "v1.0 released", "environment": "production", "type": "release"}' -X POST https://app.tideways.io/api/events
    }

    /**
     * Return config file name
     *
     * @return string xml config filename
     */
    private static function getConfigName()
    {
        return PIMCORE_WEBSITE_PATH . self::CONFIG_XML;
    }

}
