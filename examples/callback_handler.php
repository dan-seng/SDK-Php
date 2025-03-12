<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Get the callback data
$callbackData = file_get_contents('php://input');
$callbackJson = json_decode($callbackData, true);

// Log the callback for debugging
file_put_contents(
  __DIR__ . '/callback_log.txt',
  date('Y-m-d H:i:s') . ' - ' . $callbackData . PHP_EOL,
  FILE_APPEND
);

// Process the callback
if ($callbackJson) {
  $resultCode = $callbackJson['Body']['stkCallback']['ResultCode'];
  $resultDesc = $callbackJson['Body']['stkCallback']['ResultDesc'];
  $merchantRequestID = $callbackJson['Body']['stkCallback']['MerchantRequestID'];
  $checkoutRequestID = $callbackJson['Body']['stkCallback']['CheckoutRequestID'];

  if ($resultCode == 0) {
    // Payment successful
    $amount = $callbackJson['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
    $mpesaReceiptNumber = $callbackJson['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
    $transactionDate = $callbackJson['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
    $phoneNumber = $callbackJson['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

    // Here you would typically:
    // 1. Update your database
    // 2. Fulfill the order
    // 3. Send confirmation to customer
    // Example:
    /*
        $db->query("UPDATE transactions SET 
            status = 'completed',
            mpesa_receipt = ?,
            transaction_date = ?,
            amount = ?
            WHERE checkout_request_id = ?",
            [$mpesaReceiptNumber, $transactionDate, $amount, $checkoutRequestID]
        );
        */
  } else {
    // Payment failed
    // Handle the failure (log it, notify admin, update database, etc.)
  }

  // Send response back to M-Pesa
  $response = [
    'ResultCode' => 0,
    'ResultDesc' => 'Callback received successfully'
  ];
  header('Content-Type: application/json');
  echo json_encode($response);
} else {
  // Invalid callback data
  http_response_code(400);
  echo json_encode(['error' => 'Invalid callback data']);
}
