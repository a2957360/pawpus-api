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

  $paymentIntent = \Stripe\PaymentIntent::create([
    'amount' => $total * 100,
    'currency' => 'cad',
  ]);

  $output = [
    'clientSecret' => $paymentIntent->client_secret,
  ];

  echo json_encode(["message"=>"success","data"=>$output]);
  exit();
} catch (Error $e) {
  echo $e;
  echo json_encode(["message"=>"stripe error"]);
  exit();
}