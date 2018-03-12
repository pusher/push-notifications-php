<?php
namespace Pusher\PushNotifications;
class PushNotifications {
  const SDK_VERSION = "0.10.2";

  public function __construct($options) {
    $this->options = $options;
    if (!array_key_exists("instanceId", $this->options)) {
      throw new \Exception("Required 'instanceId' in Pusher\PushNotifications constructor options");
    }
    if ($this->options["instanceId"] == "") {
      throw new \Exception("'instanceId' cannot be the empty string");
    }
    if (!array_key_exists("secretKey", $this->options)) {
      throw new \Exception("Required 'secretKey' in Pusher\PushNotifications constructor options");
    }
    if ($this->options["secretKey"] == "") {
      throw new \Exception("'secretKey' cannot be the empty string");
    }
    if (!array_key_exists("endpoint", $this->options)) {
        $this->options["endpoint"] = "https://" . $options["instanceId"] . ".pushnotifications.pusher.com";
    }
  }

  public function publish($interests, $publish_request) {
    if (count($interests) == 0) {
      throw new \Exception("The interests array must not be empty");
    }

    $publish_request['interests'] = $interests;
    $body_string = json_encode($publish_request);

    $url = $this->options["endpoint"] . '/publish_api/v1/instances/' . $this->options["instanceId"] . '/publishes';
    $curl_handle = curl_init();

    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $body_string);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($body_string),
      'Authorization: Bearer ' . $this->options["secretKey"],
      'X-Pusher-Library: pusher-push-notifications-php ' . self::SDK_VERSION,
    ));

    $response_body = curl_exec($curl_handle);
    $response_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

    if ($response_body === false) {
      throw new \Exception('exec_curl error: '.curl_error($curl_handle)."\n");
    }
    if (400 <= $response_status || $response_status <= 500) {
      $error_body = json_decode($response_body);
      $bad_json = json_last_error() !== JSON_ERROR_NONE;
      $bad_schema =
        !ARRAY_KEY_EXISTS('error', $error_body) ||
        !ARRAY_KEY_EXISTS('description', $error_body);

      if ($bad_json || $bad_schema) {
        throw new \Exception('The server returned an unknown error response');
      }

      throw new \Exception("{$error_body->error}: {$error_body->description}");
    }

    $json_response = json_decode($response_body);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception('json_decode error: ' . json_last_error_msg());
    }

    return $json_response;
  }
}
