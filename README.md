Tideways Pimcore Plugin
================================================

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
