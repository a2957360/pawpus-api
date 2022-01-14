<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //网站url
    $websiteLink = WEBSITE_LINK;

    $userId=$data['userId'];
    $itemOrderId=$data['itemOrderId'];
    $orderState=$data['orderState'];

    //orderState 0:未支付;1:待发货;2:已发货;3:已收货;4:退款中;5:退款成功;6:拒绝退款
    $stmt = $pdo->prepare("UPDATE `itemOrderTable` SET `orderState`='$orderState'
                           WHERE `userId`='$userId' AND `itemOrderId`='$itemOrderId' AND `orderState`<'$orderState'");
    $stmt->execute();
    if($stmt != null){
        if($stmt->rowCount() != 0){
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
