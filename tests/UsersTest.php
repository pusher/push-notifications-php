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
    $expectedSDK = "pusher-push-notifications-php 1.1.2";

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

  public function testPublishToUsersShouldErrorIfUserIdsNotArray() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'userIds' must be an array");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      null,
      array(
        "apns" => array("aps" => array(
          "alert" => "Hello!",
        )),
        "fcm" => array("notification" => array(
          "title" => "Hello!",
          "body" => "Hello, world!",
        )),
      )
    );
  }

  public function testPublishToUsersShouldErrorIfNoUserIds() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Publishes must target at least one user");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      [],
      array(
        "apns" => array("aps" => array(
          "alert" => "Hello!",
        )),
        "fcm" => array("notification" => array(
          "title" => "Hello!",
          "body" => "Hello, world!",
        )),
      )
    );
  }

  public function testPublishToUsersShouldErrorIfTooManyUserIds() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Number of user ids exceeds maximum");

    $userIds = [];
    for($i = 0; $i < 1001; $i++) {
      array_push($userIds, "user-" . $i);
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      $userIds,
      array(
        "apns" => array("aps" => array(
          "alert" => "Hello!",
        )),
        "fcm" => array("notification" => array(
          "title" => "Hello!",
          "body" => "Hello, world!",
        )),
      )
    );
  }

  public function testPublishToUsersShouldErrorIfUserIdNotString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("not a string");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      [null],
      array(
        "apns" => array("aps" => array(
          "alert" => "Hello!",
        )),
        "fcm" => array("notification" => array(
          "title" => "Hello!",
          "body" => "Hello, world!",
        )),
      )
    );
  }

  public function testPublishToUsersShouldErrorIfUserIdTooLong() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("longer than the maximum");

    $userIdLength = 165;
    $userId = "";
    for($i = 0; $i < $userIdLength; $i++) {
      $userId = $userId . 'A';
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      [$userId],
      array(
        "apns" => array("aps" => array(
          "alert" => "Hello!",
        )),
        "fcm" => array("notification" => array(
          "title" => "Hello!",
          "body" => "Hello, world!",
        )),
      )
    );
  }

  public function testPublishToUsersShouldErrorIfUserIdIsEmptyString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");

    $userId = "";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      [$userId],
      array(
        "apns" => array("aps" => array(
          "alert" => "Hello!",
        )),
        "fcm" => array("notification" => array(
          "title" => "Hello!",
          "body" => "Hello, world!",
        )),
      )
    );
  }

  public function testPublishToUsersShouldErrorIfPublishBodyNotArray() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'publishBody' must be an array");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publishToUsers(
      ["user-0001"],
      null
    );
  }

  public function testPublishToUsersShouldErrorIfBadJsonReturned() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("unexpected server error");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=200,
        $headers=["Content-Type", "application/json"],
        $body='<notjson></notjson>'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
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
  }

  public function testPublishToUsersShouldErrorIf4xxErrorReturned() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("error_type: error_description");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=400,
        $headers=["Content-Type", "application/json"],
        $body='{"error": "error_type", "description": "error_description"}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
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
  }

  public function testPublishToUsersShouldErrorIf5xxErrorReturned() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("error_type: error_description");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=500,
        $headers=["Content-Type", "application/json"],
        $body='{"error": "error_type", "description": "error_description"}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
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
  }

  public function testPublishToUsersShouldErrorIfBadErrorJson() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("unexpected server error");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=400,
        $headers=["Content-Type", "application/json"],
        $body='<notjson></notjson>'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
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
  }

  public function testPublishToUsersShouldErrorIfBadErrorSchema() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("unexpected server error");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=400,
        $headers=["Content-Type", "application/json"],
        $body='{"notAnError": true}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
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
  }

  public function testDeleteUserShouldMakeRequestIfValid() {
    // Record history
    $container = [];
    $history = GuzzleHttp\Middleware::history($container);

    // Create mock
    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=200,
        $headers=[],
        $body=''
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
    $result = $pushNotifications->deleteUser("user-0001");

    $expectedMethod = 'DELETE';
    $expectedUrl = implode([
      'https://a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com/',
      'customer_api/v1/instances/a11aec92-146a-4708-9a62-8c61f46a82ad/users/user-0001'
    ]);

    $expectedHost = "a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com";
    $expectedContentType = "application/json";
    $expectedAuth = "Bearer EIJ2EESAH8DUUMAI8EE";
    $expectedSDK = "pusher-push-notifications-php 1.1.2";

    $request = $container[0]["request"];
    $this->assertNotNull($request, "Request should not be null");

    $method = $request->GetMethod();
    $url = (string) $request->GetUri();
    $headers = $request->GetHeaders();

    $this->assertEquals($expectedMethod, $method, "Method should be DELETE");
    $this->assertEquals($expectedUrl, $url);

    $this->assertEquals($expectedHost, $headers["Host"][0],
      "Host should be <instanceId>.pushnotifications.pusher.com");
    $this->assertEquals($expectedAuth, $headers["Authorization"][0],
      "Auth header should be bearer token");
    $this->assertEquals($expectedSDK, $headers["X-Pusher-Library"][0],
      "SDK header should be pusher-push-notifications-php <version>");
  }

  public function testDeleteUserShouldErrorIfUserIdNotAString() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = false;

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("User id must be a string");
    $token = $pushNotifications->deleteUser($userId);
  }

  public function testDeleteUserShouldErrorIfUserIdEmpty() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = "";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("User id cannot be the empty string");
    $token = $pushNotifications->deleteUser($userId);
  }

  public function testDeleteUserShouldErrorIfUserTooLong() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";

    $userIdLength = 165;
    $userId = "";
    for($i = 0; $i < $userIdLength; $i++) {
      $userId = $userId . 'A';
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("longer than the maximum");
    $token = $pushNotifications->deleteUser($userId);
  }

  public function testDeleteUserShouldNotErrorIfBadJsonReturned() {
    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=200,
        $headers=[],
        $body='<notjson></notjson>'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
    $client = new GuzzleHttp\Client(['handler' => $handler]);

    // Make request
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ), $client);
    $result = $pushNotifications->deleteUser("user-0001");
  }

  public function testDeleteUserShouldErrorIf4xxErrorReturned() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("error_type: error_description");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=400,
        $headers=["Content-Type", "application/json"],
        $body='{"error": "error_type", "description": "error_description"}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
    $client = new GuzzleHttp\Client(['handler' => $handler]);

    // Make request
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ), $client);
    $result = $pushNotifications->deleteUser("user-0001");
  }

  public function testDeleteUserShouldErrorIf5xxErrorReturned() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("error_type: error_description");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=500,
        $headers=["Content-Type", "application/json"],
        $body='{"error": "error_type", "description": "error_description"}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
    $client = new GuzzleHttp\Client(['handler' => $handler]);

    // Make request
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ), $client);
    $result = $pushNotifications->deleteUser("user-0001");
  }

  public function testDeleteUserShouldErrorIfBadErrorJson() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("unexpected server error");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=400,
        $headers=["Content-Type", "application/json"],
        $body='<notjson></notjson>'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
    $client = new GuzzleHttp\Client(['handler' => $handler]);

    // Make request
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ), $client);
    $result = $pushNotifications->deleteUser("user-0001");
  }

  public function testDeleteUserShouldErrorIfBadErrorSchema() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("unexpected server error");

    $mock = new GuzzleHttp\Handler\MockHandler([
      new GuzzleHttp\Psr7\Response(
        $status=400,
        $headers=["Content-Type", "application/json"],
        $body='{"notAnError": true}'
      )
    ]);
    $handler = GuzzleHttp\HandlerStack::create($mock);
    $client = new GuzzleHttp\Client(['handler' => $handler]);

    // Make request
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ), $client);
    $result = $pushNotifications->deleteUser("user-0001");
  }

  public function testGenerateTokenShouldReturnToken() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = "user-0001";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $tokenArray = $pushNotifications->generateToken($userId);
    $this->assertInternalType('array', $tokenArray);

    $token = $tokenArray['token'];
    $this->assertInternalType('string', $token);

    $decodedToken = JWT::decode($token, $secretKey, array("HS256"));

    $expectedIssuer = "https://a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com";
    $expectedSubject = $userId;

    $this->assertEquals($expectedIssuer, $decodedToken->iss);
    $this->assertEquals($expectedSubject, $decodedToken->sub);

    $expiry = new DateTime("@$decodedToken->exp");
    $now = new DateTime();

    $this->assertGreaterThan($now, $expiry);
  }

  public function testGenerateTokenShouldErrorIfUserIdNotAString() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = false;

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("User id must be a string");
    $token = $pushNotifications->generateToken($userId);
  }

  public function testGenerateTokenShouldErrorIfUserIdEmpty() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";
    $userId = "";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("User id cannot be the empty string");
    $token = $pushNotifications->generateToken($userId);
  }

  public function testGenerateTokenShouldErrorIfUserTooLong() {
    $instanceId = "a11aec92-146a-4708-9a62-8c61f46a82ad";
    $secretKey = "EIJ2EESAH8DUUMAI8EE";

    $userIdLength = 165;
    $userId = "";
    for($i = 0; $i < $userIdLength; $i++) {
      $userId = $userId . 'A';
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => $instanceId,
      "secretKey" => $secretKey,
    ));

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("longer than the maximum");
    $token = $pushNotifications->generateToken($userId);
  }
}
