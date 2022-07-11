<?php
use PHPUnit\Framework\TestCase;

final class InterestsTest extends TestCase {
  public function testPublishToInterestsShouldMakeRequestIfValid(): void {
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
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ], $client);
    $result = $pushNotifications->publishToInterests(
      ["donuts"],
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
      'publish_api/v1/instances/a11aec92-146a-4708-9a62-8c61f46a82ad/publishes/interests'
    ]);

    $expectedHost = "a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com";
    $expectedContentType = "application/json";
    $expectedAuth = "Bearer EIJ2EESAH8DUUMAI8EE";
    $expectedSDK = "pusher-push-notifications-php 2.0";

    $expectedBody = [
      "interests" => ["donuts"],
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

  public function testPublishToInterestsShouldErrorIfInterestsNotArray(): void {
    $this->expectException(TypeError::class);
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      null,
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfNoInterests(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Publishes must target at least one interest");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      [],
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfTooManyInterests(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Number of interests exceeds maximum");

    $interests = [];
    for($i = 0; $i < 101; $i++) {
      $interests[] = "interest-" . $i;
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      $interests,
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfInterestNotString(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("not a string");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      [null],
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfInterestTooLong(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("longer than the maximum");

    $interestLength = 165;
    $interest = "";
    for($i = 0; $i < $interestLength; $i++) {
      $interest .= 'A';
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      [$interest],
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfInterestContainsForbiddenChar(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("contains a forbidden character");

    $interest = "/donuts";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      [$interest],
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfInterestIsEmptyString(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");

    $interest = "";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      [$interest],
      [
        "apns" => ["aps" => [
          "alert" => "Hello!",
        ]],
        "fcm" => ["notification" => [
          "title" => "Hello!",
          "body" => "Hello, world!",
        ]],
      ]
    );
  }

  public function testPublishToInterestsShouldErrorIfPublishBodyNotArray(): void {
    $this->expectException(TypeError::class);
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $pushNotifications->publishToInterests(
      ["donuts"],
      null
    );
  }

  public function testPublishToInterestsShouldErrorIfBadJsonReturned(): void {
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
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ], $client);
    $result = $pushNotifications->publishToInterests(
      ["donuts"],
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

  public function testPublishToInterestsShouldErrorIf4xxErrorReturned(): void {
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
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ], $client);
    $result = $pushNotifications->publishToInterests(
      ["donuts"],
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

  public function testPublishToInterestsShouldErrorIf5xxErrorReturned(): void {
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
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ], $client);
    $result = $pushNotifications->publishToInterests(
      ["donuts"],
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

  public function testPublishToInterestsShouldErrorIfBadErrorJson(): void {
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
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ], $client);
    $result = $pushNotifications->publishToInterests(
      ["donuts"],
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

  public function testPublishToInterestsShouldErrorIfBadErrorSchema(): void {
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
    $pushNotifications = new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ], $client);
    $result = $pushNotifications->publishToInterests(
      ["donuts"],
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
}
