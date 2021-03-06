GZERO CMS [![Build Status](https://travis-ci.org/GrupaZero/cms.svg?branch=master)](https://travis-ci.org/GrupaZero/cms) [![Coverage Status](https://coveralls.io/repos/GrupaZero/cms/badge.png)](https://coveralls.io/r/GrupaZero/cms)
===

### Testing

To run the tests you need PHP >=7.1 and the following extensions:
- xml
- curl
- mbstring
- pgsql
- gd

To run tests, copy .env.example file to .env.testing and put your database credentials into it.

To run tests you can use one of these commands:

##### whole suit

`composer test`

##### single file

`composer test tests/functional/api/ContentCest.php`

##### single test

`composer test tests/functional/api/ContentCest.php:shouldBeAbleToGetCategoryChildren`

##### single test with --debug

`composer test-debug tests/functional/api/ContentCest.php:shouldBeAbleToGetCategoryChildren`

###Quality
```
./vendor/bin/phpmd src/ text phpmd.xml
./vendor/bin/phpcs --standard=ruleset.xml src/ -n --colors
```
