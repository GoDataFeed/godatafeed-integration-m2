

# Installation Instructions using Composer

## Pre-installation steps

* Create a backup of your shop before proceeding to install.

### Manual Installation

* Sign in to your server via SSH

* *cd* into your Magento installation directory

* Ensure <a href="https://getcomposer.org/" target=_blank>Composer</a> is installed. 
	* See directions here: https://devdocs.magento.com/guides/v2.0/install-gde/prereq/integrator_install_composer.html

* Ensure your Access Keys are setup and ready to use. Follow the steps here if you don't have them setup: https://devdocs.magento.com/guides/v2.0/install-gde/prereq/connect-auth.html

* Ensure your credentials are setup. The official <a href="http://repo.magento.com/" target=_blank>http://repo.magento.com/</a> is private so you must authenticate. To use Composer, you can use auth.json file for authentication in your magento root directory, or place it in the <a href="https://getcomposer.org/doc/03-cli.md#composer-home" target=_blanks>COMPOSER_HOME</a> directory for better security.

*The basic app.json file looks like this:*

```json
{
    "http-basic": {
        "repo.magento.com": {
			"username": "<your public Magento Connect key>",
			"password": "<your private Magento Connect key>"
        }
    }
}
```
[For more instructions on setting up auth, click here.](For%20more%20instructions,%20see%20https://devdocs.magento.com/guides/v2.0/install-gde/prereq/dev_install.html#instgde-prereq-compose-clone-auth)

 * Checkout the GoDataFeed extension from https://marketplace.magento.com.
 * Once you have checked out, go to My Profile > My Products > My Purchases. Look for GoDataFeed and click on Technical details.
 
 ![GoDataFeed m2 extension technical details](https://s3.amazonaws.com/static.godatafeed.com/content/docs/GoDataFeed_FeedManagement_Install.png)
 
 * Run the following commands:
 * `composer require godatafeed/godatafeed-product-integration-module:<Component version> --no-update`
 * `composer update`
 * `php bin/magento setup:upgrade`
 * `php bin/magento setup:di:compile`
 
 # Installation Instructions via FTP

## Pre-installation steps

* Create a backup of your shop before proceeding to install.

### Manual Installation

* Sign in to your server via SSH

* *cd* into your Magento installation directory

* Upload *[GoDataFeed.tar.gz](https://github.com/GoDataFeed/godatafeed-integration-m2/releases/latest)* into your Magento installation directory

* Extract the contents to */app/code* directory
  * Example: *tar -xvzf GoDataFeed.tar.gz -C ./app/code*

* Enable the extension: *php -f bin/magento module:enable --clear-static-content GoDataFeed_Product_Integration*

* Upgrade the Magento installation: *php -f bin/magento setup:upgrade*

* Compile the Magento installation: *php -f bin/magento setup:di:compile*

# Configuration

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

# Uninstall

* To completely disable the module, run: *php bin/magento module:disable GoDataFeed_Product_Integration*

# Troubleshooting
 * [Visit our help center for troubleshooting or more information.](https://help.godatafeed.com/hc/en-us/sections/115000914112)
