Laravel4 Extended Workbench
===========================
This is an extension to the native workbench command provided by Laravel4
###Build Status
**Travis CI:** [![Build Status](https://travis-ci.org/eviweb/laravel4-workbench.png?branch=master)]
(https://travis-ci.org/eviweb/laravel4-workbench)   
**Scrutinizer CI:** [![Scrutinizer Quality Score]
(https://scrutinizer-ci.com/g/eviweb/laravel4-workbench/badges/quality-score.png?s=20885ad142635ecdf98cefaf5cd3c00cacc4365b)]
(https://scrutinizer-ci.com/g/eviweb/laravel4-workbench/)     

How to install
==============
Manually using Composer
-----------------------
*   Run `composer require eviweb/laravel4-workbench:dev-master`     
*   add `'evidev\laravel4\extensions\workbench\WorkbenchServiceProvider'` in the
`providers` section of your `app/config/app.php` configuration file     

Using Laravel Package Installer
-------------------------------
*   Run `php artisan package:install eviweb/laravel4-workbench:dev-master`     

How to use
==========
Once the package is installed, run `artisan config:publish eviweb/laravel4-workbench`,
then edit the configuration file `app/config/packages/laravel4-workbench/config.php`.   
Use the command `artisan workbench [options] vendor/package` as you done before.    
This will generate your plugin skeleton.    

Available options
-----------------
    
    --resources     Create Laravel specific directories
    --psr0          Specify a specific PSR-0 compliant namespace mapping
    --ns            Specify a custom namespace for this package
    
**This feature extension ensures backward compatibility with the native implementation**    
This means that running `artisan workbench vendor/package` or `artisan workbench --resources vendor/package`
would give you the same result as before.   