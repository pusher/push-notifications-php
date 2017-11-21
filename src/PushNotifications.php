<?php
namespace \Pusher\PushNotifications;
class PushNotifications {
  const SDK_VERSION = "0.0.1";

  public function __construct($options) {
    $this->options = $options + array(
      "endpoint" => "https://errol-server-production.herokuapp.com",
    );
    if (!array_key_exists("instanceId", $this->options)) {
      throw new \Exception("Required 'instanceId' in Pusher\PushNotifications constructor options");
    }
    if (!array_key_exists("secretKey", $this->options)) {
      throw new \Exception("Required 'secretKey' in Pusher\PushNotifications constructor options");
    }
  }

  public function publish($body_json) {
    if (!array_key_exists("interests", $body_json)) {
      throw new \Exception("Required 'interests' in Pusher\PushNotifications->publish options");
    }
    $curl_handle = curl_init();
    $url = $this->options["endpoint"] . '/publish_api/v1/instances/' . $this->options["instanceId"] . '/publishes';
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
    $body_string = json_encode($body_json);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $body_string);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($body_string),
      'Authorization: Bearer ' . $this->options["secretKey"],
      'X-Pusher-Library: pusher-push-notifications-php ' . self::SDK_VERSION,
    ));
    $response_body = curl_exec($curl_handle);
    $response_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    if ($response_body === false || $response_status < 200 || 400 <= $response_status) {
      throw new \Exception('exec_curl error: '.curl_error($curl_handle)."\n");
    }
    $json_response = json_decode($response_body);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception('json_decode error: ' . json_last_error_msg());
    }
    return $json_response;
  }
}
