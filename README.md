# querymaker

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


This small library, allows you to create simple SQL queries to use with PDO easily. Just using methods with simple db command names, you can create seamless statements and key value array to use in execution.

## Motivation

When using PDO, writing queries are prone to syntax and parameter errors. To prevent them in simple queries you can use this library. 

## Requirements

Strictly requires PHP 7.4.

## Install

Via Composer

``` bash
$ composer require midorikocak/querymaker
```

## Usage

There are starter methods to create a query, such as `SELECT` and `UPDATE`. 

### Select

``` php
$queryMaker = new midorikocak\querymaker();
$queryMaker->select('users');
echo $queryMaker->getQuery;
```

The above example will output:

``` sql
SELECT * FROM users
```

### Select with fields
Fields to select can be specified as well:

``` php
$queryMaker->select('users', ['id', 'email']);
echo $queryMaker->getQuery;
```

The above example will output:

``` sql
SELECT id, email FROM users
```

### Where 

To specify `WHERE` clauase use  `where($key, $value)` method.

``` php
$queryMaker>select('users', ['id', 'email'])->where('id', 3);
echo $queryMaker->getQuery;
echo $queryMaker->getStatement;
```

The above example will output:

``` sql
SELECT id, email FROM users WHERE id='3'
SELECT id, email FROM users WHERE id=:id
```

### AND and OR 

Contraints such as `AND` and `OR`, are methods as well. `and($key, $value)` and `or($key, $value)`

``` php
$queryMaker->select('users', ['id', 'email'])->where('id', 3)->and('email', 'mtkocak@gmail.com')->or('username', 'midori');
echo $queryMaker->getQuery;
echo $queryMaker->getStatement;
```

The above example will output:

``` sql
SELECT id, email FROM users WHERE id='3' OR username='midori'
SELECT id, email FROM users WHERE id=:id OR username=:username
```

It's also possible to get values as key value pair to easily execute.

``` php
$db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);


$queryMaker->select('users', ['id', 'email'])->where('id', 3)->and('email', 'mtkocak@gmail.com')->or('username', 'midori');

$statement = $db->prepare($query->getStatement());

$statement->execute($query->getParams());
```

### Update 

To specify `UPDATE` operation, handy `update()` method, expects a key value array. All statement params are generated thoroughly. 

``` php
$queryMake->update('users', ['email' => 'mtkocak@gmail.com', 'username' => 'midorikocak'])->where('id', 3);
echo $queryMaker->getQuery;
echo $queryMaker->getStatement;
```

The above example will output:

``` sql
UPDATE users SET email='mtkocak@gmail.com', username='midorikocak' WHERE id='3'
UPDATE users SET email=:email, username=:username WHERE id=:id
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mtkocak@gmail.com instead of using the issue tracker.

## Credits

- [Midori Kocak][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/midorikocak/querymaker.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/midorikocak/querymaker/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/midorikocak/querymaker.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/midorikocak/querymaker.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/midorikocak/querymaker.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/midorikocak/querymaker
[link-travis]: https://travis-ci.org/midorikocak/querymaker
[link-scrutinizer]: https://scrutinizer-ci.com/g/midorikocak/querymaker/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/midorikocak/querymaker
[link-downloads]: https://packagist.org/packages/midorikocak/querymaker
[link-author]: https://github.com/midorikocak
[link-contributors]: ../../contributors
