# PHP SDK for Pusher Push Notifications

## Installation

[Get Composer](http://getcomposer.org/),
[then get the `pusher-push-notifications` Composer package](https://packagist.org/packages/pusher/pusher-push-notifications):

```bash
$ composer require pusher/pusher-push-notifications
```

This SDK depends on [the cURL PHP module](http://php.net/manual/en/curl.installation.php)
and [the JSON PHP module](http://php.net/manual/en/json.installation.php).

## Use

```php
<?php
include 'src/PushNotifications.php';
$pushNotifications = new Pusher\PushNotifications(array(
  "instanceId" => "YOUR_INSTANCE_ID_HERE",
  "secretKey" => "YOUR_SECRET_HERE",
  "endpoint" => "https://errol-server-production.herokuapp.com",
));
$publishResponse = $pushNotifications->publish(array(
  "interests" => array("donuts"),
  "apns" => array("aps" => array(
    "alert" => "Hello!",
  )),
));
echo("Published with Publish ID: " . $publishResponse->publishId . "\n");
```
