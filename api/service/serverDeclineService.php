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
    $serviceRefuseReason=$data['serviceRefuseReason'];

    //orderState 0:待付款;1:待商家确认;2:等待寄送;3:寄养中;4:服务结束;5:已取消；6：商家拒绝
    $stmt = $pdo->prepare("UPDATE `serviceOrderTable` SET `orderState`='6',`serviceRefuseReason`='$serviceRefuseReason'
                           WHERE `serverId`='$serverId' AND `serviceOrderId`='$serviceOrderId' AND `orderState`='0'");
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
