<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");
  require 'vendor/autoload.php';
  use PayPalCheckoutSdk\Core\PayPalHttpClient;
  use PayPalCheckoutSdk\Core\ProductionEnvironment;
  use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $clientId=PAYPAL_CLIENT_ID;
    $clientSecret=PAYPAL_SECRET;

    $orderId=$data['orderId'];
    $userId = $data['userId'];
    $orderType = $data['orderType'];
    if(isset($orderId)){
      if($orderType == 0){
        $sql = "SELECT `serviceOrderTotalPyament` AS `total`,`serviceOrderNo` AS `orderNo` From `serviceOrderTable` WHERE `serviceOrderId` = '$orderId' AND `userId` = '$userId'";
      }else{
        $sql = "SELECT `total`,`orderNo` From `itemOrderTable` WHERE `itemOrderId` = '$orderId' AND `userId` = '$userId'";
      }
      //获取订单信息
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $totalPrice = $row['total'];
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      $returnUrl = 'https://'.$_SERVER['SERVER_NAME']."/api/paypalFinish.php?orderId=".$orderId."&orderType=".$orderType;

      if(!isset($totalPrice)){
        echo json_encode(["message"=>"wrong order"]);
        exit();
      }
    }
    
    $environment = new ProductionEnvironment($clientId, $clientSecret);
    $client = new PayPalHttpClient($environment);

    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = [
                         "intent" => "CAPTURE",
                         "purchase_units" => [[
                             // "reference_id" => "test_ref_id1",
                             "amount" => [
                                 "value" => $totalPrice,
                                 "currency_code" => "CAD"
                             ]
                         ]],
                         "application_context" => [
                              // "cancel_url" => "https://example.com/cancel",
                              "return_url" => $returnUrl
                         ]
                     ];
    try {
        // Call API with your client and get a response for your call
        $response = $client->execute($request);
        // If call returns body in response, you can get the deserialized version from the result attribute of the response
        $data['paypalId'] = $orderToken = $response->result->id;
        $data['links'] = $response->result->links[1]->href;
        // $stmt = $pdo->prepare("UPDATE `orderTable` SET `orderToken`='$orderToken' WHERE `orderId` = '$orderNumber' AND `orderState` = '0'");
        // $stmt->execute();
        echo json_encode(["message"=>"success","data"=>$data]);
        exit();
    }catch (HttpException $ex) {
        echo $ex->statusCode;
        print_r($ex->getMessage());
    }

  }
?>

