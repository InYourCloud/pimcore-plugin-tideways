Tideways Pimcore Plugin
================================================

[![Codacy Badge](https://www.codacy.com/project/badge/219fb4f776944b4ebaf770889133d39e)](https://www.codacy.com/app/basilicom/pimcore-plugin-tideways)
[![Dependency Status](https://www.versioneye.com/php/basilicom-pimcore-plugin:tideways/1.0.2/badge.svg)](https://www.versioneye.com/php/basilicom-pimcore-plugin:tideways/1.0.2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/basilicom/pimcore-plugin-tideways/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/basilicom/pimcore-plugin-tideways/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/basilicom/pimcore-plugin-tideways/badges/build.png?b=master)](https://scrutinizer-ci.com/g/basilicom/pimcore-plugin-tideways/build-status/master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/27503bf4-789a-4856-bc83-783e24c2e6af/mini.png)](https://insight.sensiolabs.com/projects/27503bf4-789a-4856-bc83-783e24c2e6af)

Developer info: [Pimcore at basilicom](http://basilicom.de/en/pimcore)

## Synopsis

This Pimcore http://www.pimcore.org plugin simplifies using
and configuring the Tideways Profiler.

## Code Example / Method of Operation

If installed and enabled, the following Tideways properties
are configured via the website/var/config/tideways.xml file:

* \Tideways\Profiler::detectFramework(\Tideways\Profiler::FRAMEWORK_ZEND_FRAMEWORK1);
* \Tideways\Profiler::start($apiKey, $sampleRate);
* \Tideways\Profiler::watch($watcher); // all configured watchers from the XML file

## Motivation

Tideways works "out of the box" with ZF1 Framework detection.
In order to configure certain aspects - mainly custom span timeline
events - additional code is needed. This plugin simplifies this
process by adding a layer in between the Tideways API calls and
an easy XML based configuration file accessable via the Pimcore
Plugin management system.

## Installation

Install the Tideways system on your server, see: https://tideways.io/profiler/docs/getting-started/installation

Make sure you disable the Tideways auto-start in your php.ini: tideways.auto_start=No (The plugin takes care of that.)

Add "basilicom-pimcore-plugin/tideways" as a requirement to the
composer.json in the toplevel directory of your Pimcore installation.

Example:

    {
        "require": {
            "basilicom-pimcore-plugin/tideways": ">=1.0.0"
        }
    }
    
Install the plugin via the Pimcore Extension Manager. Press the "Configure" button of the
Tideways plugin from within the Extension Manager and set the "apiKey" property accordingly.

In order to transmit info to the Tideways servers, set the "enabled" property to "1", too.

Limit the sample rate by changing the "sampleRate".

If you want to profile/trace backend requests, too: Set the "excludeBackend" property in
the xml file to "0".

If you want to profile/trace CLI requests, too: Set the "excludeCli" property in
the xml file to "0" (this is needed for tracing maintenance.php runs).
  
You can add additional method watchers - take a look at the example section in the
config file.

Optionally, upload the sample package config file "tideways-pimcore.xml" via the
Tideways UI for custom package configuration - to be configured here:  

  https://app.tideways.io/o/basilicom/[TIDEWAYS-APPLICATION-NAME]/settings/packages

See: https://github.com/QafooLabs/profiler-packages/blob/master/package.xsd

## API Reference

The following static methods are provided as a wrapper for the original
Tideways functions:
 
* \Tideways\Plugin::setTransactionName(string $transactionName)
* \Tideways\Plugin::setCustomVariable(string $variable, mixed $value)

Not implemented, yet:

* \Tideways\Plugin::createEvent(string $eventName, string $environment='production', string $type='release')

## Tests

* none

## Todo

* Implement exception handler tracing 
* Implement event creation

## Contributors

* Tim Jagodzinski <tim.jagodzinski@basilicom.de>
* Christoph Luehr <christoph.luehr@basilicom.de>

## License

* BSD-3-Clause
