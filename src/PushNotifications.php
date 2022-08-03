<?php
namespace Pusher\PushNotifications;

use Firebase\JWT\JWT;
use GuzzleHttp;

/**
 * Pusher Push Notifications client class
 * Used to publish notifications to the Pusher Push Notifications API
 * http://www.pusher.com/push-notifications
 */
class PushNotifications {
  const SDK_VERSION = "2.0.0";
  const MAX_INTERESTS = 100;
  const MAX_INTEREST_LENGTH = 164;
  const INTEREST_REGEX = "/^(_|-|=|@|,|\\.|;|[A-Z]|[a-z]|[0-9])+$/";
  const MAX_USERS = 1000;
  const MAX_USER_ID_LENGTH = 164;
  const AUTH_TOKEN_DURATION_SECS = 24 * 60 * 60;

  private GuzzleHTTP\Client $client;

  public function __construct(private array $options, GuzzleHTTP\Client|null $client = null) {
    if (!array_key_exists("instanceId", $this->options)) {
      throw new \Exception("Required 'instanceId' in Pusher\PushNotifications constructor options");
    }
    if (!is_string($this->options["instanceId"])) {
      throw new \Exception("'instanceId' must be a string");
    }
    if ($this->options["instanceId"] === "") {
      throw new \Exception("'instanceId' cannot be the empty string");
    }

    if (!array_key_exists("secretKey", $this->options)) {
      throw new \Exception("Required 'secretKey' in Pusher\PushNotifications constructor options");
    }
    if (!is_string($this->options["secretKey"])) {
      throw new \Exception("'secretKey' must be a string");
    }
    if ($this->options["secretKey"] === "") {
      throw new \Exception("'secretKey' cannot be the empty string");
    }

    if (!array_key_exists("endpoint", $this->options)) {
        $this->options["endpoint"] = "https://" . $options["instanceId"] . ".pushnotifications.pusher.com";
    } else {
      if (!is_string($this->options["endpoint"])) {
        throw new \Exception("'endpoint' must be a string");
      }
      if ($this->options["endpoint"] === "") {
        throw new \Exception("'endpoint' cannot be the empty string");
      }
    }

    if (!$client) {
      $this->client = new GuzzleHttp\Client();
    } else {
      $this->client = $client;
    }
  }

  private function makeRequest(string $method, string $path, array $pathParams, array|null $body = null): mixed {
    $escapedPathParams = [];
    foreach ($pathParams as $k => $v) {
      $escapedPathParams[$k] = urlencode($v);
    }

    $endpoint = $this->options["endpoint"];
    $interpolatedPath = strtr($path, $escapedPathParams);
    $url = $endpoint . $interpolatedPath;

    try {
      $response = $this->client->request(
        $method,
        $url,
        [
          "headers" => [
            "Authorization" => "Bearer " . $this->options["secretKey"],
            "X-Pusher-Library" => "pusher-push-notifications-php " . PushNotifications::SDK_VERSION
          ],
          "json" => $body
        ]
      );
    } catch (\GuzzleHttp\Exception\BadResponseException $e) {
      $response = $e->GetResponse();
      $parsedResponse = json_decode($response->GetBody());
      $badJSON = $parsedResponse === null;
      if (
        $badJSON ||
        !property_exists($parsedResponse, 'error') ||
        !property_exists($parsedResponse, 'description')
      ) {
        throw new \Exception("An unexpected server error has occurred");
      }
      throw new \Exception("{$parsedResponse->error}: {$parsedResponse->description}");
    }

    $parsedResponse = json_decode($response->GetBody());

    return $parsedResponse;
  }

    /**
     * @param array $interests
     * @param array<string> $publishRequest
     * @return mixed
     * @throws \Exception
     */
  public function publishToInterests(array $interests, array $publishRequest): mixed {
    if (count($interests) === 0) {
      throw new \Exception("Publishes must target at least one interest");
    }
    if (count($interests) > PushNotifications::MAX_INTERESTS) {
      throw new \Exception("Number of interests exceeds maximum of " . PushNotifications::MAX_INTERESTS);
    }

    foreach($interests as $interest) {
      if (!is_string($interest)) {
        throw new \Exception("Interest \"$interest\" is not a string");
      }
      if (mb_strlen($interest) > PushNotifications::MAX_INTEREST_LENGTH) {
        throw new \Exception("Interest \"$interest\" is longer than the maximum length of " . PushNotifications::MAX_INTEREST_LENGTH . " chars.");
      }
      if ( $interest === '' ) {
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
    $path = '/publish_api/v1/instances/INSTANCE_ID/publishes/interests';
    $pathParams = [
      'INSTANCE_ID' => $this->options["instanceId"]
    ];
    $response = $this->makeRequest("POST", $path, $pathParams, $publishRequest);

    if ($response === null) {
      throw new \Exception("An unexpected server error has occurred");
    }

    return $response;
  }

  public function publishToUsers(array $userIds, array $publishRequest): mixed {
    if (count($userIds) === 0) {
      throw new \Exception("Publishes must target at least one user");
    }
    if (count($userIds) > PushNotifications::MAX_USERS) {
      throw new \Exception("Number of user ids exceeds maximum of " . PushNotifications::MAX_USERS);
    }

    foreach($userIds as $userId) {
      $this->checkUserId($userId);
    }

    $publishRequest['users'] = $userIds;
    $path = '/publish_api/v1/instances/INSTANCE_ID/publishes/users';
    $pathParams = [
      'INSTANCE_ID' => $this->options["instanceId"]
    ];
    $response = $this->makeRequest("POST", $path, $pathParams, $publishRequest);

    if ($response === null) {
      throw new \Exception("An unexpected server error has occurred");
    }

    return $response;
  }

  public function deleteUser(string $userId): void {
    $this->checkUserId($userId);

    $path = '/customer_api/v1/instances/INSTANCE_ID/users/USER_ID';
    $pathParams = [
      'INSTANCE_ID' => $this->options["instanceId"],
      'USER_ID' => $userId
    ];
    $this->makeRequest("DELETE", $path, $pathParams);
  }

  public function generateToken(string $userId): array {
      $this->checkUserId($userId);

    $instanceId = $this->options["instanceId"];
    $secretKey = $this->options["secretKey"];

    $issuer = "https://$instanceId.pushnotifications.pusher.com";
    $claims = [
      "iss" => $issuer,
      "sub" => $userId,
      "exp" => time() + PushNotifications::AUTH_TOKEN_DURATION_SECS
    ];

    $token = JWT::encode($claims, $secretKey, 'HS256');

    return [
      "token" => $token
    ];
  }

  private function checkUserId(string $userId): void {
      if ($userId === '') {
          throw new \Exception("User id cannot be the empty string");
      }
      if (mb_strlen($userId) > PushNotifications::MAX_USER_ID_LENGTH) {
          throw new \Exception("User id \"$userId\" is longer than the maximum length of " . PushNotifications::MAX_USER_ID_LENGTH . " chars.");
      }
  }

  public function getClient(): GuzzleHttp\Client
  {
      return $this->client;
  }
}
