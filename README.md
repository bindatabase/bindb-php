Version: 0.2.0

# What is this package for?

Have you ever thought of knowing information such as: credit card level, issuer (bank) name,
card country, etc... by analyzing a credit card number?
Well, now you can do it as easy as 1-2-3.

```php
$bin = new \Riad\Bin();
$response = $bin->lookup(437776);

var_dump($response);
```

And here's the magic, the $response will be:

```
object(stdClass)#2 (7) {
  ["bin"]=>
  int(437776)
  ["country_code"]=>
  string(2) "MA"
  ["vendor"]=>
  string(4) "VISA"
  ["type"]=>
  string(5) "DEBIT"
  ["level"]=>
  string(7) "CLASSIC"
  ["is_prepaid"]=>
  bool(false)
  ["issuer"]=>
  string(17) "ATTIJARIWAFA BANK"
}
```

# Requirements
This package requires `PHP 5.6.0` or greater.

# License
This project is released under the [MIT](https://github.com/riadloukili/bindb-php/blob/master/LICENSE)
license.

# Installation

## Download the package.

If you are using Composer, run the following command:

```batch
composer require riadloukili/bindb-php
```

**NOTE**: You can also [download](https://github.com/riadloukili/bindb-php/archive/master.zip)
the package directly and extract it in your web directory.

## Include the package into your project.

```php
require_once 'vendor/autoload.php';
```

If you're not using Composer.

```php
require_once 'DIR/src/Riad/Bin.php';
```

# Usage

If you have a secret token with active plan, you should provide it in the constructor, if you don't,
keep it void or you can subscribe to a plan in [BinDB](https://bindb.me/).

```php
$bin = new \Riad\Bin($secret_token);

$bin = new \Riad\Bin(); // If you don't have a secret token
```

**NOTE**: Not providing a secret token will restrict you to 1200* requests daily.

In order to get a bin information you should use `::get(int|string $bin)` method.

```php
$response = $bin->get(437776);
```

You can also use `::search(int|string $bin)` or `::lookup(int|string $bin)` which are aliases of `::get()`

**NOTE**: If you want raw json response, you may use `::raw(int\string $bin)`.

You may also get only the fields you need by specifying them with the method `::fields(array $fields)`.

```php
$response = $bin->fields(['bin', 'vendor', 'issuer', 'country_code'])
                ->search(437776);
```

Available fields are: `bin`, `country_code`, `vendor`, `type`, `level`, `issuer`, `is_prepaid`.

# Error Handling

By default, if any error occurs the returned value is NULL.

If you want to change that behavior to throwing exceptions, you may set the error mode to ON
by calling the method `error(bool $error_mode)` just before calling either `::search()`,
`::lookup()` or `::get()`

```php
$bin->error(true);

$response = $bin->get(...);
```

# Bonus

We also provide a **non-stable** method `::query(string $query)` which allows you to use an
SQL-like syntax in bin lookups.

```php
$response = $bin->query("SELECT country_code,issuer,level FROM bins WHERE bin = ?")
                ->run([437776]);
// Other example
$response = $bin->query("SELECT * FROM bins WHERE bin = 437776")
                ->run();
```

# Contribution

This package is open-source, so everyone can contribute.

All contributions are welcome, any contributor will get 1 free month of Basic plan + High priority
support plus their name, email and website on our credits page.


*: The number of daily requests may increase or decrease depending on server and bandwidth status.
