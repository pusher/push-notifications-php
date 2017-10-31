<?php
namespace Pusher;
class PushNotifications {
  public function __construct() {
    $this->instanceId = 'abc'; // FIXME
  }

  public function publish() {
    echo "publishing\n";

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, 'https://localhost:8080/publish_api/v1/instances/' . $this->instanceId . '/publishes');
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'X-Pusher-Library: pusher-push-notifications-php',
    ));
    curl_setopt($curl_handle, CURLOPT_POST, 1);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, json_encode(array(
      "interests" => array("donuts"),
      "apns" => array("aps" => "FIXME"),
    )));

    $response_body = curl_exec($curl_handle);
    $response_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    echo $response_body;
    echo $response_status;
    if ($response_body === false || $response_status < 200 || 400 <= $response_status) {
        $this->log('ERROR: exec_curl error: '.curl_error($curl_handle));
    }
  }
}
