<?php
use PHPUnit\Framework\TestCase;

final class PushNotificationsTest extends TestCase {
  public function testConstructorShouldAcceptValidParams(): void {
    new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
    $this->assertTrue(true);
  }

  public function testConstructorShouldErrorIfOptionsNotArray(): void {
    $this->expectException(TypeError::class);
    new Pusher\PushNotifications\PushNotifications(null);
  }

  public function testConstructorShouldErrorIfInstanceIdNotGiven(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Required 'instanceId'");
    new Pusher\PushNotifications\PushNotifications([
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
  }

  public function testConstructorShouldErrorIfInstanceIdNotString(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'instanceId' must be a string");
    new Pusher\PushNotifications\PushNotifications([
      "instanceId" => null,
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
  }

  public function testConstructorShouldErrorIfInstanceIdEmptyString(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");
    new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "",
      "secretKey" => "EIJ2EESAH8DUUMAI8EE",
    ]);
  }

  public function testConstructorShouldErrorIfSecretKeyNotGiven(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Required 'secretKey'");
    new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
    ]);
  }

  public function testConstructorShouldErrorIfSecretKeyNotString(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("'secretKey' must be a string");
    new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => null,
    ]);
  }

  public function testConstructorShouldErrorIfSecretKeyEmptyString(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("cannot be the empty string");
    new Pusher\PushNotifications\PushNotifications([
      "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
      "secretKey" => "",
    ]);
  }

  public function testConstructorShouldErrorIfWrongClientTypeIsGiven(): void
  {
      $this->expectException(TypeError::class);
      new Pusher\PushNotifications\PushNotifications(
        [
          "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
          "secretKey" => "EIJ2EESAH8DUUMAI8EE",
        ],
        []
      );
  }

  public function testConstructorShouldMakeANewClientIfNoneIsGiven(): void
  {
      $notification = new Pusher\PushNotifications\PushNotifications(
        [
          "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
          "secretKey" => "EIJ2EESAH8DUUMAI8EE",
        ],
  null
      );

      $this->assertInstanceOf(\GuzzleHttp\Client::class, $notification->getClient());
  }

  public function testConstructShouldUseGivenClient(): void
  {
      $url = 'https://pusher.com/';
      $client = new \GuzzleHttp\Client(['base_url' => $url]);

      $notification = new Pusher\PushNotifications\PushNotifications(
        [
          "instanceId" => "a11aec92-146a-4708-9a62-8c61f46a82ad",
          "secretKey" => "EIJ2EESAH8DUUMAI8EE",
        ],
        $client
      );

      $this->assertSame($client, $notification->getClient());
  }
}
