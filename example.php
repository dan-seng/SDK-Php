<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mpesa\MpesaClient;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize the M-Pesa client
$mpesa = new MpesaClient(
  $_ENV['MPESA_CONSUMER_KEY'],
  $_ENV['MPESA_CONSUMER_SECRET'],
  $_ENV['MPESA_ENVIRONMENT']
);

try {
  // Initiate STK Push
  $result = $mpesa->stkPush(
    '0712345678',  // Phone number
    1000,          // Amount
    'https://your-callback-url.com/callback',  // Callback URL
    'INV001',      // Account reference
    'Payment for order #INV001'  // Transaction description
  );

  echo "STK Push initiated successfully:\n";
  print_r($result);

  // Check transaction status after a few seconds
  sleep(10);
  $status = $mpesa->checkTransactionStatus($result['CheckoutRequestID']);

  echo "\nTransaction status:\n";
  print_r($status);
} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
}