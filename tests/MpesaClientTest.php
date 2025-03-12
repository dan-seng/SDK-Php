<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Mpesa\MpesaClient;

class MpesaClientTest extends TestCase
{
  private MpesaClient $mpesa;

  protected function setUp(): void
  {
    $this->mpesa = new MpesaClient(
      'test_consumer_key',
      'test_consumer_secret',
      'sandbox'
    );
  }

  public function testPhoneNumberFormatting()
  {
    $method = new \ReflectionMethod(MpesaClient::class, 'formatPhoneNumber');
    $method->setAccessible(true);

    // Test Kenyan number without country code
    $this->assertEquals('254712345678', $method->invoke($this->mpesa, '0712345678'));

    // Test number with country code
    $this->assertEquals('254712345678', $method->invoke($this->mpesa, '254712345678'));

    // Test number without leading zero
    $this->assertEquals('254712345678', $method->invoke($this->mpesa, '712345678'));
  }

  public function testInvalidPhoneNumber()
  {
    $method = new \ReflectionMethod(MpesaClient::class, 'formatPhoneNumber');
    $method->setAccessible(true);

    $this->expectException(\Exception::class);
    $method->invoke($this->mpesa, '123'); // Too short
  }

  // Add more tests for other methods...
}
