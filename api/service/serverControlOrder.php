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

    $serverId=$data['userId'];
    $serviceOrderId=$data['serviceOrderId'];
    $orderState=$data['orderState'];

    //orderState 0:待付款;1:待商家确认;2:等待寄送;3:寄养中;4:服务结束;5:已取消；6：商家拒绝
    $stmt = $pdo->prepare("UPDATE `serviceOrderTable` SET `orderState`='$orderState'
                           WHERE `serverId`='$serverId' AND `serviceOrderId`='$serviceOrderId' AND `orderState`<'$orderState'");
    $stmt->execute();
    if($stmt != null){
        if($stmt->rowCount() != 0){
          if($orderState == 4){
              $stmt = $pdo->prepare("UPDATE `userTable` 
                                      SET `serverPoints`= `serverPoints` + (SELECT `serviceOrderTotalPrice` - `serverChargeFee` FROM `serviceOrderTable` WHERE `serverId`='$serverId' AND `serviceOrderId`='$serviceOrderId')
                                     WHERE `userId`='$serverId'");
              $stmt->execute();
          }
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
