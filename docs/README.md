# Installation Instructions

## Pre-installation steps

* Create a backup of your shop before proceeding to install.

### Manual Installation

* Sign in to your server via SSH

* *cd* into your Magento installation directory

* Upload *GoDataFeed.tar.gz* into your Magento installation directory

* Extract the contents to */app/code* directory
  * Example: *tar -xvzf GoDataFeed.tar.gz -C ./app/code*

* Enable the extension: *php -f bin/magento module:enable --clear-static-content GoDataFeed_Product_Integration*

* Upgrade the Magento installation: *php -f bin/magento setup:upgrade*

* Compile the Magento installation: *php -f bin/magento setup:di:compile*

### Configuration

* Login to your Magento Admin Dashboard

* Go to: System > Extensions > Integrations

* Click: **Add New Integration**
  * Add a 'Name'
    * Example: *GoDataFeed_Integration*
  * Under *Current User Identity Verification* add your password
* Click: **API**
  * Under *Resources* grant access to:
    * Products > Inventory
    * Stores > Settings > All Stores
* Click: The down arrow next to the **Save** button
  * Click: **Save & Activate**
  * Click: **Allow**
  * Copy all of your Integration Tokens:
    * *Consumer Key*
    * *Consumer Secret*
    * *Access Token*
    * *Access Token Secret*

* Go to: Stores > All Stores

* Click on the *Store View* you wish to use for the integration
  * Copy the *store_id* value which will be visible in the URL
    * Example URL: *http://www.your_magento_store.com/admin/system_store/editStore/store_id/**4**/key*

### Uninstall

* To completely disable the module, run: *php bin/magento module:disable GoDataFeed_Product_Integration*