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

    const TRANSACTION_NAME_MAINTENANCE = 'Maintenance';

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

        if (!class_exists('Tideways\Profiler', false)) {
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

        if (
            ($config->tideways->get('excludeCli', '1') == '1')
            && (substr(php_sapi_name(), 0, 3) == 'cli')
        ) {
            return;
        }

        \Tideways\Profiler::detectFramework(\Tideways\Profiler::FRAMEWORK_ZEND_FRAMEWORK1);

        $sampleRate = (int)$config->tideways->get('sampleRate', 10);
        \Tideways\Profiler::start($apiKey, $sampleRate);

        foreach ($config->tideways->watchers->watcher as $watcher) {
            \Tideways\Profiler::watch($watcher);
        }

        self::$isEnabled = true;

        \Pimcore::getEventManager()->attach("system.maintenance", array($this, 'performMaintenance'));

    }

    /**
     * Gathers metrics and sends them off to librato
     */
    public function performMaintenance()
    {
        if (self::$isEnabled) {
            \Tideways\Profiler::setTransactionName(self::TRANSACTION_NAME_MAINTENANCE);
        }
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
     * @param string $name
     * @param string $environment
     * @param string $type
     * @return bool true on success
     */
    public static function createEvent($name, $environment='production', $type='release')
    {
        $data = array(
            "apiKey": "api key here",
            "name": $name,
            "environment": $environment,
            "type" => $type
        );
        
        $dataString = json_encode($data);
        
        $curlOptions = array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => "https://app.tideways.io/api/events",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($dataString))
            ),
            CURLOPT_POSTFIELDS => $dataString,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FRESH_CONNECT => true
        );

        $curlHandle = curl_init(); 

        curl_setopt_array($curlHandle, $curlOptions); 

        if (! $result = curl_exec($curlHandle)) { 
            // trigger_error(curl_error($curlHandle)); 
            return false;
        } 
        curl_close($curlHandle); 
        return true;         
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
