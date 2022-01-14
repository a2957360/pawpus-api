<?php
// include("../../include/sql.php");
include("../../include/conf/config.php");
require_once "../stripe-php-master/init.php";


// This is a sample test API key. Sign in to see examples pre-filled with your key.
\Stripe\Stripe::setApiKey(STRIPE_SECRET);

http_response_code(200);
header('content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

$data = file_get_contents('php://input');
$data = json_decode($data,true);

$price = $data['price'];
try {

$success_url = ALPHAPAY_REDIRECT_URL;
$cancel_url = CANCEL_URL;
$date = date("Y-m-d");
  $session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'payment_method_types' => ['acss_debit'],
    // or you can take multiple payment methods with
    // 'payment_method_types' => ['card', 'acss_debit', ...]
    'line_items' => [[
      'price_data' => [
        'currency' => 'usd',
        // To accept `acss_debit`, all line items must have currency: cad, usd
        'currency' => 'cad',
        'product_data' => [
          'name' => 'Finestudio Subscription',
        ],
        'unit_amount' => (float)$price*100,
      ],
      'quantity' => 1,
    ]],
    'mode' => 'payment',
    'payment_method_options' => [
      'acss_debit' => [
        'mandate_options' => [
          'payment_schedule' => 'interval',
          'interval_description' => $date,
          'transaction_type' => 'personal',
        ],
      ],
    ],
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
  ]);

  $output = [
    'sessionId' => $session->id,
  ];

  echo json_encode(["message"=>"success","data"=>$session]);
  exit();
} catch (Error $e) {
  echo $e;
  echo json_encode(["message"=>"stripe error"]);
  exit();
}