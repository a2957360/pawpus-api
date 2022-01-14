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

    $serviceId=$data['serviceId'];
    $userId=$data['userId'];

    $list = array();
    $stmt = $pdo->prepare("SELECT `serviceOrderId`
                           From `serviceOrderTable` 
                           WHERE `serviceId`='$serviceId' AND `serverId`='$userId' AND `orderState` NOT IN(4,5)");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        if(isset($row['serviceOrderId'])){
          echo json_encode(["message"=>"201","errorCode"=>201]);
          exit();
        }
      }
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("DELETE FROM `serviceTable`
                           WHERE `serviceId`='$serviceId' AND `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
        if($stmt->rowCount() != 0){
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"111","errorCode"=>111]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
