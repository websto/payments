#Payments Liqpay,PrivatBank,Bitcoin
=============================
With the help of [PHPStorm](https://www.jetbrains.com/phpstorm/)

##Requirements##

 * [Composer](https://getcomposer.org) is required for installation

##Installation##

Run the command below to install via Composer

```shell
composer require websto/payments
```

##Getting Started##

#### Add to `websto/payments/config/paymentconf.php`

#### Add to `config/app.php`

Add the service provider to `providers`:

```php
'providers' => [
    // ...
    Websto\Payments\PaymentServiceProvider::class,
],
```


