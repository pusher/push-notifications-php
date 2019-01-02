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
}
