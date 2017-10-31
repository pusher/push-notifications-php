<?php
include 'src/PushNotifications.php';
$pushNotifications = new Pusher\PushNotifications();
$pushNotifications->publish();
