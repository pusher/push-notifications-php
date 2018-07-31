<?php
namespace Pusher\PushNotifications;

use GuzzleHttp;

/**
 * Pusher Push Notifications client class
 * Used to publish notifications to the Pusher Push Notifications API
 * http://www.pusher.com/push-notifications
 */
class PushNotifications {
  const SDK_VERSION = "1.0.0";
  const MAX_INTERESTS = 100;
  const MAX_INTEREST_LENGTH = 164;
  const INTEREST_REGEX = "/^(_|-|=|@|,|\\.|;|[A-Z]|[a-z]|[0-9])+$/";

  private $options = array();
  private $client = null;

  public function __construct($options, $client = null) {
    $this->options = $options;
    if (!is_array($this->options)) {
      throw new \Exception("Options parameter must be an array");
    } 
    if ($client == null) {
      $this->client = new GuzzleHTTP\Client();
    } else {
      $this->client = $client;
    }

    if (!array_key_exists("instanceId", $this->options)) {
      throw new \Exception("Required 'instanceId' in Pusher\PushNotifications constructor options");
    }
    if (!is_string($this->options["instanceId"])) {
      throw new \Exception("'instanceId' must be a string");
    }
    if ($this->options["instanceId"] == "") {
      throw new \Exception("'instanceId' cannot be the empty string");
    }

    if (!array_key_exists("secretKey", $this->options)) {
      throw new \Exception("Required 'secretKey' in Pusher\PushNotifications constructor options");
    }
    if (!is_string($this->options["secretKey"])) {
      throw new \Exception("'secretKey' must be a string");
    }
    if ($this->options["secretKey"] == "") {
      throw new \Exception("'secretKey' cannot be the empty string");
    }

    if (!array_key_exists("endpoint", $this->options)) {
        $this->options["endpoint"] = "https://" . $options["instanceId"] . ".pushnotifications.pusher.com";
    } else {
      if (!is_string($this->options["endpoint"])) {
        throw new \Exception("'endpoint' must be a string");
      }
      if ($this->options["endpoint"] == "") {
        throw new \Exception("'endpoint' cannot be the empty string");
      }
    }
  }

  public function publish($interests, $publishRequest) {
    if (!is_array($interests)) {
      throw new \Exception("'interests' must be an array");
    }
    if (count($interests) == 0) {
      throw new \Exception("Publishes must target at least one interest");
    }
    if (count($interests) > PushNotifications::MAX_INTERESTS) {
      throw new \Exception("Number of interests exceeds maximum of " . PushNotifications::MAX_INTERESTS);
    }
    if(!is_array($publishRequest)) {
      throw new \Exception("'publishBody' must be an array");
    }

    foreach($interests as $interest) {
      if (!is_string($interest)) {
        throw new \Exception("Interest \"$interest\" is not a string");
      }
      if (strlen($interest) > PushNotifications::MAX_INTEREST_LENGTH) {
        throw new \Exception("Interest \"$interest\" is longer than the maximum length of " . PushNotifications::MAX_INTEREST_LENGTH . " chars.");
      }
      if (strlen($interest) == 0) {
        throw new \Exception("Interest names cannot be the empty string");
      }
      if (!preg_match(PushNotifications::INTEREST_REGEX, $interest)) {
        throw new \Exception(implode([
          "Interest \"$interest\" contains a forbidden character.",
          " Allowed characters are: ASCII upper/lower-case letters,",
          " numbers or one of _=@,.;-"
        ]));
      }
    }

    $publishRequest['interests'] = $interests;
    $url = $this->options["endpoint"] . '/publish_api/v1/instances/' . $this->options["instanceId"] . '/publishes';
    try {
      $response = $this->client->request(
        'POST',
        $url,
        [
          "headers" => [
            "Authorization" => "Bearer " . $this->options["secretKey"],
            "X-Pusher-Library" => "pusher-push-notifications-php " . PushNotifications::SDK_VERSION
          ],
          "json" => $publishRequest
        ]
      );
    } catch (\GuzzleHttp\Exception\BadResponseException $e) {
      $response = $e->GetResponse();
      $parsedResponse = json_decode($response->GetBody());
      $badJSON = $parsedResponse == null;
      if (
        $badJSON ||
        !ARRAY_KEY_EXISTS('error', $parsedResponse) ||
        !ARRAY_KEY_EXISTS('description', $parsedResponse)
      ) {
        throw new \Exception("An unexpected server error has occurred");
      }
      throw new \Exception("{$parsedResponse->error}: {$parsedResponse->description}");
    }

    $parsedResponse = json_decode($response->GetBody());
    if ($parsedResponse == null) {
      throw new \Exception("An unexpected server error has occurred");
    }

    return $parsedResponse;
  }
}
