# Solute plugin for OXID eSales shop
* Version: 1.0.x

## Requirements
* php >= 8.0
* OXID 7.0.x CE/PE/EE/B2B
* composer >= 2.7.x

## Installation
* add to the shop composer.json
  * section `repositories`: `"oxid_solute": { "type": "vcs", "url": "https://github.com/solute/oxid-billiger-de.git" }`
  * section `require`: `"unitm/oxid_solute": "dev-b-7.0.x"`
* execute on console (Or use the script install.sh or respectively install.bat. Requires a composer.phar file.)
  * `composer clearcache`
  * `composer update -n` (you will need credentials for the gitlab of unit-m)
  * `vendor/bin/oe-console oe:module:install vendor/unitm/oxid_solute`
  * `vendor/bin/oe-console oe:module:activate oxid_solute`
* Module settings
  * add your
    * bearer token
    * solute shop id
    * solute feed id
* Check the value mapping

## Modul update
* execute on console
  * `vendor/bin/oe-console oe:module:deactivate oxid_solute`
  * `composer clearcache`
  * `composer update - confirm only this module with 'y', all the rest can be reject with 'N'`
  * `vendor/bin/oe-console oe:module:install vendor/unitm/oxid_solute`
  * `vendor/bin/oe-console oe:module:activate oxid_solute`

## Cronjobs
### Send all articles to the api
  * call (console): `php oxideshop/vendor/unitm/oxid_solute/src/Cron/CronSendToApi.php [shopId=ShopId]`
  * `[shopId=x]` is an optional parameter. If it is not provided, shop id 1 is used as default value. Example = `shopId=2`
  * before sending to api each article data will be validated

## Tests
All tests have to be run from the console, except delivery test (dlvtest.php).

### Validator test
* call: `php oxideshop/vendor/unitm/oxid_solute/src/Tests/ValidatorTest.php`
* This test proofs, if the validators works properly as expected

### Landingpage test
* First, set your host in `src/Tests/LandingpageTest.php` line 16
* call: `php oxideshop/vendor/unitm/oxid_solute/src/Tests/LandingpageTest.php`
* This test sends some demo data simulating the execute of click a link from solute, e.g. on billiger.de.

### Conversion test
* First, set your host in `src/Tests/ConversionTest.php` line 16
* call: `php oxideshop/vendor/unitm/oxid_solute/src/Tests/ConversionTest.php`
* This tests sends some demo data simulating the execute of tracking an order.

### Import test articles
With this import you can easily import test articles to the shop to test all functions of this module.

**DO NOT EXECUTE THIS IMPORT ON PRODUCTION SYSTEMS!**

**Datasets will not be deleted in any case. Orphaned datasets could occur, while multiple imports. To avoid this, it is recommended to do a database backup before importing, to get a clean database again after reimport.**

* call: `php oxideshop/vendor/unitm/oxid_solute/src/Tests/CsvArticleImport/Import.php`
* Requieres a csv file, generated from an excel file like the example in `oxideshop/vendor/unitm/oxid_solute/src/Tests/CsvArticleImport/ImportFile/`
* The import csv file must have the name `ArticleImport.csv` and the encryption must be ANSI. (The conversion to utf will be done by the import, so you do not need convert the encryption after export from excel.)
* The import reads line by line from the csv file. Because of this even very large files can be processed without problems with system requirements. Be aware of the script execution time. 
* The import supports multiple shops (for OXID EE / B2B)
* The import actually does NOT support multiple languages, only language 1 is supported.
* The import supports actually 4 levels of categories and 4 images each article.
* Images, which are referenced in the csv, are not imported, but have to be copied manually to the correct master directories.
* The article id is generated from articlenumber, ean, title and shop id.
* The category-, attribute- and manufacturer-id are generated from title and shop id.
* If datasets exists with same OXID (criterias see above), e.g. while executing the import serveral times, this datasets will be 

### DeliveryTime MapData Test
This test proofs the generation of delivery time info from the fields oxmindeltime, oxmaxdeltime and oxdeltimeunit from the table oxarticles.
* call: `php oxideshop/source/modules/unitm/oxid_solute/src/Tests/DeliveryTimeMapDataTest.php`
* Note: The testlogic is copied from \UnitM\Solute\Model\mapData::getDeliveryTime to get tested.
