php composer.phar clearcache
php composer.phar update -n

php vendor/bin/oe-console oe:module:install-configuration source/modules/unitm/oxid_solute
php vendor/bin/oe-console oe:module:apply-configuration

php vendor/bin/oe-console oe:module:deactivate oxid_solute
php vendor/bin/oe-console oe:module:activate oxid_solute
