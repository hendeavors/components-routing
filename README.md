# Components-Routing - Using new Laravel Routing features

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hendeavors/components-routing/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hendeavors/components-routing/?branch=master)

This library enables the ability to use some of the new routing features only available in newer versions of the laravel framework such as signed urls (available starting in Laravel 5.6).

Require this package in your composer.json and update composer.

    composer require endeavors/components-routing

# Installation

After updating composer, add the ServiceProvider to the providers array in config/app.php

```php
Endeavors\Components\Routing\FoundationServiceProvider::class,
```

# Creating a signed route

You can create a signed route using the URL Facade.

```php
URL::signedRoute('foo', ['id' => 1, 'username' => 'bob']);
```

For convenience, a helper can be used.

```php
signed_route('foo', ['id' => 1]);
```

# Validation

Validation of the signature is performed on the entire url.

```php
use Illuminate\Support\Facades\Request;

public function verifyEmail()
{
    if (Request::hasValidSignature()) {
        // verify the email
    }
}
```

You may also check if the request is invalid.

```php
use Illuminate\Support\Facades\Request;

public function verifyEmail()
{
    if (Request::hasInvalidSignature()) {
        // DON'T verify the email
    }
}
```

Validation can be performed only if specific parameters exist.

```php
use Illuminate\Support\Facades\Request;

public function verifyEmail()
{
    // validate the signature if the email parameter exists
    if (Request::hasValidParameterSignature(['email'])) {
        // verify the email
    }
}
```

Again, you may check if the request has an invalid signature.

```php
use Illuminate\Support\Facades\Request;

public function verifyEmail()
{
    // validate the signature if the email parameter exists
    if (Request::hasInvalidParameterSignature(['email'])) {
        // DON'T verify the email
    }
}
```
