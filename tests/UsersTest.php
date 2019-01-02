<?php
use PHPUnit\Framework\TestCase;
use Firebase\JWT\JWT;

final class UsersTest extends TestCase {
  public function testPublishToUsersShouldMakeRequestIfValid() {
    // Record history
    $container = [];
    $history = GuzzleHttp\Middleware::history($container);

    // Create mock
    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=200,
        $headers=["Content-Type", "application/json"],
        $body='{"publishId": "pub-1234"}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
    $handler->push($history);
    $client = new GuzzleHttp\Client(['handler' => $handler]);

    // Make request
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ), $client);
    $result = $pushNotifications->publishToUsers(
      ["user-0001"],
      [
        "apns" => [
          "aps" => [
            "alert" => "Hello!"
          ]
        ],
        "fcm" => [
          "notification" => [
            "title" => "Hello!",
            "body" => "Hello, world!"
          ]
        ]
      ]
    );

    $expectedMethod = 'POST';
    $expectedUrl = implode([
      'https://a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com/',
      'publish_api/v1/instances/a11aec92-146a-4708-9a62-8c61f46a82ad/publishes/users'
    ]);

    $expectedHost = "a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com";
    $expectedContentType = "application/json";
    $expectedAuth = "Bearer EIJ2EESAH8DUUMAI8EE";
    $expectedSDK = "pusher-push-notifications-php 1.0.0";

    $expectedBody = [
      "users" => ["user-0001"],
      "apns" => [
        "aps" => [
          "alert" => "Hello!"
        ]
      ],
      "fcm" => [
        "notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!"
        ]
      ]
    ];
    $expectedPublishId = "pub-1234";

    $request = $container[0]["request"];
    $method = $request->GetMethod();
    $url = (string) $request->GetUri();
    $headers = $request->GetHeaders();
    $body = json_decode((string) $request->GetBody(), true);

    $this->assertEquals($expectedMethod, $method, "Method should be POST");
    $this->assertEquals($expectedUrl, $url);

    $this->assertEquals($expectedHost, $headers["Host"][0],
      "Host should be <instanceId>.pushnotifications.pusher.com");
    $this->assertEquals($expectedContentType, $headers["Content-Type"][0],
      "Content type should be application/json");
    $this->assertEquals($expectedAuth, $headers["Authorization"][0],
      "Auth header should be bearer token");
    $this->assertEquals($expectedSDK, $headers["X-Pusher-Library"][0],
      "SDK header should be pusher-push-notifications-php <version>");

    $this->assertEquals($expectedBody, $body);
    $this->assertEquals($expectedPublishId, $result->publishId);
  }

  public function testAuthenticateUserShouldReturnToken() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = "user-0001";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $token = $pushNotifications->authenticateUser($userId);
    $decodedToken = JWT::decode($token, $secretKey, array("HS256"));

    $expectedIssuer = "https://a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com";
    $expectedSubject = $userId;

    $this->assertEquals($expectedIssuer, $decodedToken->iss);
    $this->assertEquals($expectedSubject, $decodedToken->sub);

    $expiry = new DateTime("@$decodedToken->exp");
    $now = new DateTime();

    $this->assertGreaterThan($now, $expiry);
  }

  public function testAuthenticateUserShouldErrorIfUserIdNotAString() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = false;

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("User id must be a string");
    $token = $pushNotifications->authenticateUser($userId);
  }

  public function testAuthenticateUserShouldErrorIfUserIdEmpty() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = "";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("User id cannot be the empty string");
    $token = $pushNotifications->authenticateUser($userId);
  }
}
