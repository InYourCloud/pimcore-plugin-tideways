<?php

namespace Tideways;

use Pimcore\API\Plugin as PluginLib;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    public function init() {
        if (class_exists('Tideways\Profiler') && self::isInstalled()) {
            self:: initTidewaysWatchers();
        }
    }

    public static function initTidewaysWatchers(){
        $configXml = new \DOMDocument();
        $configXml->load(PIMCORE_PLUGINS_PATH."/Tideways/config.xml");
        $additionalWatchers = $configXml->getElementsByTagName('additionalWatcher');

        foreach($additionalWatchers as $additionalWatcher){
            tideways_span_watch($additionalWatcher->nodeValue);
        }
    }

	public static function install (){
        if(!is_dir(self::getInstallCheckPath())) {
            mkdir(self::getInstallCheckPath()); //use folder as 'installed' marker
        }

        if (self::isInstalled()) {
            return "Successfully installed.";
        } else {
            return "Could not be installed";
        }
	}
	
	public static function uninstall (){
        rmdir(self::getInstallCheckPath()); //use folder as 'installed' marker

        if (!self::isInstalled()) {
            return "Successfully uninstalled.";
        } else {
            return "Could not be uninstalled";
        }
	}

    public static function isInstalled () {
        return is_dir(self::getInstallCheckPath());
    }

    public static function getInstallCheckPath() {
        return PIMCORE_PLUGINS_PATH."/Tideways/installed";
    }
}
