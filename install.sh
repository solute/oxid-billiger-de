php composer.phar clearcache
php composer.phar update -n

php vendor/bin/oe-console oe:module:install vendor/unitm/oxid_solute

php vendor/bin/oe-console oe:module:deactivate oxid_solute
php vendor/bin/oe-console oe:module:activate oxid_so