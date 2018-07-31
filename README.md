[![Build Status](https://travis-ci.org/pusher/push-notifications-php.svg?branch=master)](https://travis-ci.org/pusher/push-notifications-php)
# PHP SDK for Pusher Beams

PHP server library for publishing notifications through Pusher Beams.

Check https://docs.pusher.com/beams/reference/server-sdk-php for more information.

## Installation

[Get Composer](http://getcomposer.org/),
[then get the `pusher/pusher-push-notifications` Composer package](https://packagist.org/packages/pusher/pusher-push-notifications):

```bash
$ composer require pusher/pusher-push-notifications
```

This SDK depends on [the JSON PHP module](http://php.net/manual/en/json.installation.php).

## Use

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$pushNotifications = new \Pusher\PushNotifications\PushNotifications(array(
  "instanceId" => "YOUR_INSTANCE_ID_HERE",
  "secretKey" => "YOUR_SECRET_HERE",
));
$publishResponse = $pushNotifications->publish(array("donuts"), array(
  "apns" => array("aps" => array(
    "alert" => "Hello!",
  )),
  "fcm" => array("notification" => array(
    "title" => "Hello!",
    "body" => "Hello, world!",
  )),
));

echo("Published with Publish ID: " . $publishResponse->publishId . "\n");
```
