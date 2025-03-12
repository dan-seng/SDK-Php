# PHP M-Pesa SDK

A simple and lightweight PHP SDK for integrating M-Pesa payments into your application.

## Features

- STK Push (Lipa Na M-Pesa Online)
- Transaction status checking
- Automatic access token management
- Phone number validation and formatting
- Environment-based configuration (Sandbox/Production)

## Installation

1. Install via Composer:

```bash
composer require mpesa/php-sdk
```

2. Copy the `.env.example` file to `.env` and update with your M-Pesa credentials:

```bash
cp .env.example .env
```

3. Update the `.env` file with your M-Pesa API credentials:

```
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_SHORTCODE=your_shortcode_here
MPESA_PASSKEY=your_passkey_here
MPESA_ENVIRONMENT=sandbox
```

## Usage

```php
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

// Initiate STK Push
try {
    $result = $mpesa->stkPush(
        '0712345678',  // Phone number
        1000,          // Amount
        'https://your-callback-url.com/callback',  // Callback URL
        'INV001',      // Account reference
        'Payment for order #INV001'  // Transaction description
    );

    // Check transaction status
    $status = $mpesa->checkTransactionStatus($result['CheckoutRequestID']);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Requirements

- PHP 7.4 or higher
- GuzzleHttp
- vlucas/phpdotenv

## Security

Remember to:

- Never commit your `.env` file
- Always use HTTPS for your callback URLs
- Validate all incoming data
- Store your API credentials securely

## License

MIT License
