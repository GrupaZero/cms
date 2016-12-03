GZERO CMS [![Build Status](https://travis-ci.org/GrupaZero/cms.svg?branch=master)](https://travis-ci.org/GrupaZero/cms) [![Coverage Status](https://coveralls.io/repos/GrupaZero/cms/badge.png)](https://coveralls.io/r/GrupaZero/cms)
===

###Testing

To run tests, copy .env.example file to .env.testing and put your database credentials into it.

To run tests you can use one of those commands:

#####whole suit

`composer test`

#####single file

`composer test tests/unit/repositories/UserRepositoryTest`

#####single test

`composer test tests/unit/repositories/UserRepositoryTest:can_delete_user`

