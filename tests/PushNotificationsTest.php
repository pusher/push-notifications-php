<?php
use PHPUnit\Framework\TestCase;

final class PushNotificationsTest extends TestCase {
  public function testConstructorShouldAcceptValidParams() {
    new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $this->assertTrue(true);
  }

  public function testConstructorShouldErrorIfOptionsNotArray() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("must be an array");
    new Pusher\PushNotifications\PushNotifications(null);
  }

  public function testConstructorShouldErrorIfInstanceIdNotGiven() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Required 'instanceId'");
    new Pusher\PushNotifications\PushNotifications(array(
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
  }

  public function testConstructorShouldErrorIfInstanceIdNotString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'instanceId' must be a string");
    new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => null,
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
  }

  public function testConstructorShouldErrorIfInstanceIdEmptyString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");
    new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
  }

  public function testConstructorShouldErrorIfSecretKeyNotGiven() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Required 'secretKey'");
    new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
    ));
  }

  public function testConstructorShouldErrorIfSecretKeyNotString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'secretKey' must be a string");
    new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => null,
    ));
  }

  public function testConstructorShouldErrorIfSecretKeyEmptyString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");
    new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "",
    ));
  }

  public function testShouldMakeRequestIfValid() {
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
    $result = $pushNotifications->publish(
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
      'publish_api/v1/instances/a11aec92-146a-4708-9a62-8c61f46a82ad/publishes'
    ]);

    $expectedHost = "a11aec92-146a-4708-9a62-8c61f46a82ad.pushnotifications.pusher.com";
    $expectedContentType = "application/json";
    $expectedAuth = "Bearer EIJ2EESAH8DUUMAI8EE";
    $expectedSDK = "pusher-push-notifications-php 1.0.0";

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

  public function testShouldErrorIfInterestsNotArray() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'interests' must be an array");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
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

  public function testShouldErrorIfNoInterests() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Publishes must target at least one interest");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
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

  public function testShouldErrorIfTooManyInterests() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Number of interests exceeds maximum");

    $interests = [];
    for($i = 0; $i < 101; $i++) {
      array_push($interests, "interest-" . $i);
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
      $interests,
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

  public function testShouldErrorIfInterestNotString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("not a string");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
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

  public function testShouldErrorIfInterestTooLong() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("longer than the maximum");

    $interestLength = 165;
    $interest = "";
    for($i = 0; $i < $interestLength; $i++) {
      $interest = $interest . 'A';
    }

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
      [$interest],
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

  public function testShouldErrorIfInterestContainsForbiddenChar() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("contains a forbidden character");

    $interest = "/donuts";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
      [$interest],
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

  public function testShouldErrorIfInterestIsEmptyString() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");

    $interest = "";

    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
      [$interest],
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

  public function testShouldErrorIfPublishBodyNotArray() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'publishBody' must be an array");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
      ["donuts"],
      null
    );
  }

  public function testShouldErrorIfResponseNotJSON() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'publishBody' must be an array");
    $pushNotifications = new Pusher\PushNotifications\PushNotifications(array(
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ));
    $pushNotifications->publish(
      ["donuts"],
      null
    );
  }

  public function testShouldErrorIfBadJsonReturned() {
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
    $result = $pushNotifications->publish(
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

  public function testShouldErrorIf4xxErrorReturned() {
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
    $result = $pushNotifications->publish(
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

  public function testShouldErrorIf5xxErrorReturned() {
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
    $result = $pushNotifications->publish(
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

  public function testShouldErrorIfErrorBadJSON() {
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
    $result = $pushNotifications->publish(
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

  public function testShouldErrorIfErrorBadSchema() {
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
    $result = $pushNotifications->publish(
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
