<?php

namespace Mpesa;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MpesaClient
{
  private string $consumerKey;
  private string $consumerSecret;
  private string $environment;
  private string $accessToken;
  private Client $client;

  private const SANDBOX_URL = 'https://sandbox.safaricom.co.ke';
  private const PRODUCTION_URL = 'https://api.safaricom.co.ke';

  public function __construct(string $consumerKey, string $consumerSecret, string $environment = 'sandbox')
  {
    $this->consumerKey = $consumerKey;
    $this->consumerSecret = $consumerSecret;
    $this->environment = $environment;
    $this->client = new Client([
      'base_uri' => $environment === 'production' ? self::PRODUCTION_URL : self::SANDBOX_URL,
      'timeout' => 30,
    ]);
    $this->generateAccessToken();
  }

  private function generateAccessToken(): void
  {
    try {
      $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
      $response = $this->client->request('GET', '/oauth/v1/generate?grant_type=client_credentials', [
        'headers' => [
          'Authorization' => 'Basic ' . $credentials
        ]
      ]);

      $result = json_decode($response->getBody()->getContents());
      $this->accessToken = $result->access_token;
    } catch (GuzzleException $e) {
      throw new \Exception('Failed to generate access token: ' . $e->getMessage());
    }
  }

  public function stkPush(
    string $phoneNumber,
    float $amount,
    string $callbackUrl,
    string $accountReference,
    string $transactionDesc = 'Payment'
  ): array {
    try {
      $response = $this->client->request('POST', '/mpesa/stkpush/v1/processrequest', [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->accessToken,
          'Content-Type' => 'application/json'
        ],
        'json' => [
          'BusinessShortCode' => $_ENV['MPESA_SHORTCODE'],
          'Password' => $this->generatePassword(),
          'Timestamp' => date('YmdHis'),
          'TransactionType' => 'CustomerPayBillOnline',
          'Amount' => $amount,
          'PartyA' => $this->formatPhoneNumber($phoneNumber),
          'PartyB' => $_ENV['MPESA_SHORTCODE'],
          'PhoneNumber' => $this->formatPhoneNumber($phoneNumber),
          'CallBackURL' => $callbackUrl,
          'AccountReference' => $accountReference,
          'TransactionDesc' => $transactionDesc
        ]
      ]);

      return json_decode($response->getBody()->getContents(), true);
    } catch (GuzzleException $e) {
      throw new \Exception('STK Push failed: ' . $e->getMessage());
    }
  }

  private function generatePassword(): string
  {
    $timestamp = date('YmdHis');
    return base64_encode($_ENV['MPESA_SHORTCODE'] . $_ENV['MPESA_PASSKEY'] . $timestamp);
  }

  private function formatPhoneNumber(string $phoneNumber): string
  {
    $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

    if (strlen($phoneNumber) === 9) {
      return '254' . $phoneNumber;
    }

    if (strlen($phoneNumber) === 10 && $phoneNumber[0] === '0') {
      return '254' . substr($phoneNumber, 1);
    }

    if (strlen($phoneNumber) === 12 && substr($phoneNumber, 0, 3) === '254') {
      return $phoneNumber;
    }

    throw new \Exception('Invalid phone number format');
  }

  public function checkTransactionStatus(string $transactionId): array
  {
    try {
      $response = $this->client->request('POST', '/mpesa/transactionstatus/v1/query', [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->accessToken,
          'Content-Type' => 'application/json'
        ],
        'json' => [
          'BusinessShortCode' => $_ENV['MPESA_SHORTCODE'],
          'Password' => $this->generatePassword(),
          'Timestamp' => date('YmdHis'),
          'CheckoutRequestID' => $transactionId
        ]
      ]);

      return json_decode($response->getBody()->getContents(), true);
    } catch (GuzzleException $e) {
      throw new \Exception('Transaction status check failed: ' . $e->getMessage());
    }
  }
}