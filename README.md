# GoDataFeed Integration Extension for Magento 2

## Installation and Configuration

Please follow the instructions in the [User Guide](/docs/README.md)

## Learn More about GoDataFeed

* [US] (<https://www.godatafeed.com>)

## Pre-Requisites

* Magento
  * [Magento 2.3 System Requirements](https://devdocs.magento.com/guides/v2.3/install-gde/system-requirements.html)
  * [Magento 2.4 System Requirements](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements.html)

* An active GoDataFeed account



## Release Notes
### [v2.0.9 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.9)

#### Enhancements 
        * Adding in use_config_manage_stock and manage_stock fields
        
### [v2.0.8 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.8)

#### Enhancements 
        * Fixed missing variable for stockItems

### ~~[v2.0.7 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.7)~~

#### Enhancements 
        * Fix for stock items using the wrong id

### ~~[v2.0.6 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.6)~~

#### Enhancements 
        * Added code for Inventory Sources and Final Price

### [v2.0.5 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.5)

#### Enhancements 
        * Updated logic to include attributes with data types

### [v2.0.4 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.4)

#### Enhancements 
        * The code has been updated by PHPCS and PHPMD code standards
        * The source code moved to root directory towards Magento marketplace requirements 

### [v2.0.3 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.3)

#### Enhancements 
        * The module can be installed from GitHub via composer
        * Performance Optimizations
        * Module successfully tested at Magento Open Source 2.3.0

### [v2.0.2 update](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/tag/v2.0.2)

### Bug Fixes
        * Fixed issue during installation, some users received the following exception:
                "Message: Class "array" does not exist. Please note that namespace must be specified"

### v2.0.1 update

#### New Release

        * This is a rewrite and new release
		* Fixed issues with custom fields as price with incorrect prices

		
### v1.4.1 update

#### Enhancements

        * This release does not have any enhancements

#### Bug Fixes

        * Added quantity field back in
        
### v1.4.0 update

#### Enhancements

        * Removed the _attribute suffix
        * Refactored to pull all attributes include price
        * Updated to pull any price field with _final as well to get the discounts applied

#### Bug Fixes

        * This release does not fix any bugs
        
### v1.3.0 update

#### Enhancements

        * Added 'composer.json' file

#### Bug Fixes

        *  Removed 'header' from Model/Product

### v1.2.0 update

#### Enhancements

        * This release does not add any new features

#### Bug Fixes

        *  Fixed 'type' filter for /product and /product/count

### v1.0.0 launch

#### Enhancements

        * This release does not add any new features

#### Bug Fixes

        * This release does not fix any bugs
