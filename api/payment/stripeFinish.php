<?php
include("../../include/sql.php");
include("../../include/conf/config.php");
require_once "../stripe-php-master/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

// http_response_code(200);
// header('content-type:application/json;charset=utf8');
// header('Access-Control-Allow-Origin: *');
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// $data = file_get_contents('php://input');
// $data = json_decode($data,true);
function print_log($val) {
  return file_put_contents('php://stderr', print_r($val, TRUE));
}
// You can find your endpoint's secret in your webhook settings
$endpoint_secret = 'whsec_cNrO2y7oSMn2srbmpasdy31Up9UOPNWe';

$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;



try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
	var_dump($e);
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
	var_dump($e);
  http_response_code(400);
  exit();
}


// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
  $session = $event->data->object;
  // Fulfill the purchase...
  $orderId =$session['metadata']['orderId'];
  $orderType =$session['metadata']['orderType'];

  $input = json_encode($session);
  $stmt = $pdo->prepare("INSERT INTO `testAlphaPay`(`Isupdate`) VALUES ('$input')");
  $stmt->execute();
  
  if($orderType == 0){
      $stmt = $pdo->prepare("UPDATE `serviceOrderTable` SET `orderState`='1',`servicePayment` = 'Stripe'
                             WHERE `serviceOrderId`='$orderId' AND `orderState`='0'");
      $stmt->execute();
  }else{
    $stmt = $pdo->prepare("UPDATE `itemOrderTable` SET `orderState`='1',`paymentType` = 'Stripe'
                           WHERE `itemOrderId`='$orderId' AND `orderState`='0'");
      $stmt->execute();
  }
}

http_response_code(200);