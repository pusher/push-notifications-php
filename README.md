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

### Configuring the SDK for your instance
```php
<?php
require __DIR__ . '/vendor/autoload.php';

$pushNotifications = new \Pusher\PushNotifications\PushNotifications(array(
  "instanceId" => "YOUR_INSTANCE_ID_HERE",
  "secretKey" => "YOUR_SECRET_HERE",
));
```

### Publishing to Device Interests
You can broadcast notifications to groups of subscribed devices using [Device Interests](https://docs.pusher.com/beams/concepts/device-interests):
```php
$publishResponse = $pushNotifications->publishToInterests(
  ["donuts"],
  [
    "apns" => [
      "aps" => [
        "alert" => "Hello!",
      ],
    ],
    "fcm" => [
      "notification" => [
        "title" => "Hello!",
        "body" => "Hello, world!",
      ],
    ],
  ]
);

echo("Published with Publish ID: " . $publishResponse->publishId . "\n");
```

### Publishing to Authenticated Users
Securely send notifications to individual users of your application using [Authenticated Users](https://docs.pusher.com/beams/concepts/authenticated-users):
```php
$publishResponse = $pushNotifications->publishToUsers(
  ["user-0001"],
  [
    "apns" => [
      "aps" => [
        "alert" => "Hello!",
      ],
    ],
    "fcm" => [
      "notification" => [
        "title" => "Hello!",
        "body" => "Hello, world!",
      ],
    ],
  ]
);

echo("Published with Publish ID: " . $publishResponse->publishId . "\n");
```
