<?php
  include("../include/sql.php");
  include("../include/conf/config.php");
  require 'vendor/autoload.php';
  use PayPalCheckoutSdk\Core\PayPalHttpClient;
  use PayPalCheckoutSdk\Core\ProductionEnvironment;
  use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

  // http_response_code(200);
  // header('content-type:application/json;charset=utf8');
  // header('Access-Control-Allow-Origin: *');
  // header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  // header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $orderId=$_GET['orderId'];
    $orderToken=$_GET['token'];
    $orderType = $data['orderType'];

    $clientId=PAYPAL_CLIENT_ID;
    $clientSecret=PAYPAL_SECRET;
    if($orderType == 0){
      $sql = "SELECT `serviceOrderTotalPyament` AS `total`,`serviceOrderNo` AS `orderNo` From `serviceOrderTable` WHERE `serviceOrderId` = '$orderId' AND `userId` = '$userId'";
    }else{
      $sql = "SELECT `total`,`orderNo`,`itemList` From `itemOrderTable` WHERE `itemOrderId` = '$orderId' AND `userId` = '$userId'";
    }
    //获取订单信息
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $totalPrice = $row['totalPrice'];
        $itemList = json_decode($row["itemList"], true);
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    
    if(!isset($totalPrice)){
      echo json_encode(["message"=>"wrong order"]);
      exit();
    }
    if($orderToken != $sqlorderToken){
      echo json_encode(["message"=>"wrong token"]);
      exit();
    }

    //给商品添加销量
    foreach ($itemList as $key => $value) {
      $itemNumber = $value['itemNumber'];
      $itemQuantity = (int)$value['itemQuantity'];
      $stmt = $pdo->prepare("UPDATE `itemTable` SET `itemSaleNum` = `itemSaleNum`+'$itemQuantity' WHERE `itemNumber` = '$itemNumber'");
      $stmt->execute();
    }

    $environment = new ProductionEnvironment($clientId, $clientSecret);
    $client = new PayPalHttpClient($environment);

    $request = new OrdersCaptureRequest($sqlorderToken);
    $request->prefer('return=representation');
    try {
        // Call API with your client and get a response for your call
        $response = $client->execute($request);
        $captureId = $response->result->purchase_units[0]->payments->captures[0]->id;
        //修改订单状态
        if($orderType == 0){
          $sql = "UPDATE `serviceOrderTable` SET `orderState`='1', `servicePayment` = 'Paypal' WHERE `orderId` = '$orderId' AND `orderState` = '0'";
        }else{
          $sql = "UPDATE `itemOrderTable` SET `orderState`='1', `paymentType` = 'Paypal' WHERE `orderId` = '$orderId' AND `orderState` = '0'";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        // If call returns body in response, you can get the deserialized version from the result attribute of the response
        // print_r($response);
    }catch (HttpException $ex) {
        // echo $ex->statusCode;
        // print_r($ex->getMessage());
    }

  }
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

  </head>
  <body>

    <div class="container">
      <div style="height: 100vh" class="row ">

        <div class="col align-self-center text-center">
          <img src="../include/image/shopAvatar.png">
          <h2 class="mt-5">Thanks for the payment!</h2>
        </div>

      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>

  </body>
</html>
