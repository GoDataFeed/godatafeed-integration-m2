# GoDataFeed Integration Extension for Magento 2

[View the Complete User Guide](./README.md)

## Learn More about GoDataFeed

* [US] (<https://www.godatafeed.com>)

## Pre-Requisites

* Magento 2.1+
  * [Magento 2 System Requirements](http://devdocs.magento.com/magento-system-requirements.html)

* Php 7.0+

* An active GoDataFeed account

## Installation and Configuration

Please follow the instructions in the [User Guide](/docs/README.md)

## Release Notes

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
