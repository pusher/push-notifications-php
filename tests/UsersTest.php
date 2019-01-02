<?php
use PHPUnit\Framework\TestCase;
use Firebase\JWT\JWT;

final class UsersTest extends TestCase {
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
