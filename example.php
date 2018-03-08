<?php
include 'src/PushNotifications.php';
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
