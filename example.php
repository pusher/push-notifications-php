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
