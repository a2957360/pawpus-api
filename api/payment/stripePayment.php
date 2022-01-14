<?php
include("../../include/sql.php");
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

try {
  $orderId = $data['orderId'];
  $userId = $data['userId'];
  //0服务 1商品
  $orderType = $data['orderType'];
  if($orderType == 0){
    $sql = "SELECT `serviceOrderTotalPyament` AS `total` From `serviceOrderTable` WHERE `serviceOrderId` = '$orderId' AND `userId` = '$userId'";
  }else{
    $sql = "SELECT `total` From `itemOrderTable` WHERE `itemOrderId` = '$orderId' AND `userId` = '$userId'";
  }
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $total = $row['total'];
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  if(!isset($total)){
    echo json_encode(["message"=>"no order"]);  
    exit();
  }

$success_url = ALPHAPAY_REDIRECT_URL;
$cancel_url = CANCEL_URL;

$checkout_session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['card'],
  'line_items' => [[
    'price_data' => [
      'currency' => 'CAD',
      'unit_amount' => $total*100,
      'product_data' => [
        'name' => 'Pawpus',
      ],
    ],
    'quantity' => 1,
  ]],
  'mode' => 'payment',
  'success_url' => $success_url,
  'cancel_url' => $cancel_url,
  'metadata'=>[
    'orderId'=>$orderId,
    'orderType'=>$orderType
  ]
]);

  $output = [
    'sessionId' => $checkout_session->id,
  ];

  echo json_encode(["message"=>"success","data"=>$output]);
  exit();
} catch (Error $e) {
  echo $e;
  echo json_encode(["message"=>"stripe error"]);
  exit();
}